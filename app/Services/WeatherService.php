<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;

class WeatherService
{
    /**
     * 指定された都市の現在の天気データを取得する
     */
    public function getCurrentWeather(?string $city = null): ?array
    {
        // デフォルト値を設定
        if (!$city) {
            $city = 'Tokyo';
        }

        // 都道府県名を英語の都市名に変換
        $city = $this->convertPrefectureToCity($city);

        $apiKey = config('services.openweathermap.key');
        if (! $apiKey) {
            \Log::error("OpenWeatherMap API Key is not set.");
            return null;
        }

        $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'ja',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error("Failed to get weather data for {$city}: " . $response->status() . " - " . $response->body());
        return null;
    }

    /**
     * 天気データから「くしゃみ発生確率」を計算する (基本確率)
     * 気温、湿度、風速、天気条件を総合的に判断
     *
     * @param array $weatherData
     * @return int 0-100
     */
    public function calculateSneezeRateFromWeather(array $weatherData): int
    {
        $sneezeRate = 0;

        // 気温による影響（寒暖差でくしゃみが出やすい）
        $temp = $weatherData['main']['temp'] ?? 20;
        if ($temp < 10) {
            $sneezeRate += 30; // 寒いとくしゃみ
        } elseif ($temp > 30) {
            $sneezeRate += 25; // 暑すぎてもムズムズ
        } elseif ($temp < 15 || $temp > 25) {
            $sneezeRate += 15; // やや不快な温度
        }

        // 湿度による影響（低湿度で鼻粘膜が乾燥）
        $humidity = $weatherData['main']['humidity'] ?? 50;
        if ($humidity < 30) {
            $sneezeRate += 35; // 乾燥でくしゃみ多発
        } elseif ($humidity < 50) {
            $sneezeRate += 20; // やや乾燥
        } elseif ($humidity > 80) {
            $sneezeRate += 10; // 高湿度でもムズムズ
        }

        // 風速による影響（花粉・ホコリが舞う）
        $windSpeed = $weatherData['wind']['speed'] ?? 0;
        if ($windSpeed > 7) {
            $sneezeRate += 25; // 強風で花粉飛散
        } elseif ($windSpeed > 4) {
            $sneezeRate += 15; // やや風あり
        } elseif ($windSpeed > 2) {
            $sneezeRate += 5; // 微風
        }

        // 天気による影響
        $weather = $weatherData['weather'][0]['main'] ?? 'Clear';
        if (in_array($weather, ['Clear', 'Clouds'])) {
            $sneezeRate += 15; // 晴れ・曇りは花粉が飛びやすい
        } elseif ($weather === 'Rain') {
            $sneezeRate -= 25; // 雨の日は花粉が少ない
        } elseif (in_array($weather, ['Mist', 'Fog', 'Haze'])) {
            $sneezeRate += 10; // もやでムズムズ
        }

        // 0-100の範囲に収める
        return min(100, max(0, $sneezeRate));
    }

    /**
     * 天気情報のみから信頼度を算出する
     * 最大信頼度は80%に設定し、不確定要素の幅を持たせる
     *
     * @param array $weatherData
     * @return int 0-100
     */
    public function calculateReliabilityFromWeather(array $weatherData): int
    {
        $reliability = 60; // 基本の信頼度

        // 必須データが存在するかチェック
        if (isset($weatherData['main']['temp']) && isset($weatherData['main']['humidity']) && isset($weatherData['wind']['speed']) && isset($weatherData['weather'][0]['main'])) {
            $reliability += 20; // 主要なデータが全て揃っていれば加点
        } else {
             // 重要な情報が一つでも欠けていたら減点
            if (!isset($weatherData['main']['temp'])) $reliability -= 10;
            if (!isset($weatherData['main']['humidity'])) $reliability -= 10;
            if (!isset($weatherData['wind']['speed'])) $reliability -= 10;
            if (!isset($weatherData['weather'][0]['main'])) $reliability -= 10;
        }

        // 地域情報の正確性（都道府県→都市名変換の精度などを考慮）
        // 現状は常に高精度と仮定し、ここでは調整なし

        return max(0, min(90, (int)$reliability)); // 最大信頼度を90%に設定
    }


    /**
     * ユーザーの鼻タイプと天気情報を組み合わせて個人のくしゃみ確率と信頼度を算出する
     *
     * @param int $baseSneezeRate 天気情報のみで算出された基本くしゃみ確率
     * @param string $noseType ユーザーの鼻タイプ名
     * @param array $weatherData 天気情報
     * @param User $user ユーザーモデル
     * @return array ['rate' => int, 'reliability' => int]
     */
    public function calculatePersonalSneezeRate(int $baseSneezeRate, string $noseType, array $weatherData, User $user): array
    {
        $personalRate = $baseSneezeRate;
        $reliability = $this->calculateReliabilityFromWeather($weatherData); // 基本信頼度は天気から

        // ユーザーの体質情報
        $allergySensitivity = $user->allergy_sensitivity ?? 0;
        $temperatureSensitivity = $user->temperature_sensitivity ?? 0;
        $weatherSensitivity = $user->weather_sensitivity ?? 0;

        // 天気情報から詳細な要素を取得
        $temp = $weatherData['main']['temp'] ?? 20;
        $humidity = $weatherData['main']['humidity'] ?? 50;
        $windSpeed = $weatherData['wind']['speed'] ?? 0;
        $weatherMain = $weatherData['weather'][0]['main'] ?? 'Clear';

        // 鼻タイプと体質情報に基づく調整
        switch ($noseType) {
            case 'マルチアラート鼻':
                // 全ての敏感度が高いので、天気情報の影響をより強く受ける
                $personalRate = $personalRate * 1.3; // 1.3倍に増幅
                $reliability += 15; // 個人の情報が加わるので信頼度も上がる
                break;
            case '花粉ハンター鼻':
                // アレルギー敏感度が高いので、花粉が飛びやすい条件で強く影響
                if ($allergySensitivity >= 4 && in_array($weatherMain, ['Clear', 'Clouds']) && $windSpeed > 3) {
                    $personalRate += 30; // 大きく加算
                }
                $reliability += 10;
                break;
            case '気候センサー鼻':
                // 温度・天気に敏感なので、気温や湿度の変化で影響
                if ($temperatureSensitivity >= 4 && ($temp >= 30 || $temp <= 10)) {
                    $personalRate += 25; // 極端な気温
                }
                if ($weatherSensitivity >= 4 && ($humidity < 40 || $humidity > 80)) { // 乾燥や高湿度に反応
                    $personalRate += 20;
                }
                $reliability += 10;
                break;
            case '敏感ノーズ':
                // 全体的に中程度の敏感さなので、天気情報の影響をやや強く受ける
                $personalRate = $personalRate * 1.1; // 1.1倍に増幅
                $reliability += 5;
                break;
            case 'バランス鼻':
                // 体質の影響が少ないので、天気情報の基本確率に大きな変化なし
                // 信頼度も基本天気情報ベース
                break;
        }

        // 個人の敏感度による微調整（各敏感度がくしゃみ確率に与える影響）
        // スコアが高いほどくしゃみ確率も高まる
        $personalRate += ($allergySensitivity * 2);
        $personalRate += ($temperatureSensitivity * 1.5);
        $personalRate += ($weatherSensitivity * 1.5);

        // 信頼度も個人の体質情報が加わることで上昇
        // 全ての体質情報が設定されていればさらに信頼度アップ
        if ($allergySensitivity > 0 || $temperatureSensitivity > 0 || $weatherSensitivity > 0) {
            $reliability += 10; // いずれかの体質情報があれば上昇
        }
        if ($allergySensitivity > 0 && $temperatureSensitivity > 0 && $weatherSensitivity > 0) {
            $reliability += 5; // すべて揃っていればさらに追加
        }


        // 上限・下限を調整
        $personalRate = max(0, min(100, (int)$personalRate));
        $reliability = max(0, min(90, (int)$reliability)); // ここでも最大信頼度を90%に設定

        return ['rate' => $personalRate, 'reliability' => $reliability];
    }

    /**
     * 都道府県名を英語の都市名に変換する
     */
    private function convertPrefectureToCity(string $prefecture): string
    {
        $prefectureMap = [
            '北海道' => 'Sapporo',
            '青森県' => 'Aomori',
            '岩手県' => 'Morioka',
            '宮城県' => 'Sendai',
            '秋田県' => 'Akita',
            '山形県' => 'Yamagata',
            '福島県' => 'Fukushima',
            '茨城県' => 'Mito',
            '栃木県' => 'Utsunomiya',
            '群馬県' => 'Maebashi',
            '埼玉県' => 'Saitama',
            '千葉県' => 'Chiba',
            '東京都' => 'Tokyo',
            '神奈川県' => 'Yokohama',
            '新潟県' => 'Niigata',
            '富山県' => 'Toyama',
            '石川県' => 'Kanazawa',
            '福井県' => 'Fukui',
            '山梨県' => 'Kofu',
            '長野県' => 'Nagano',
            '岐阜県' => 'Gifu',
            '静岡県' => 'Shizuoka',
            '愛知県' => 'Nagoya',
            '三重県' => 'Tsu',
            '滋賀県' => 'Otsu',
            '京都府' => 'Kyoto',
            '大阪府' => 'Osaka',
            '兵庫県' => 'Kobe',
            '奈良県' => 'Nara',
            '和歌山県' => 'Wakayama',
            '鳥取県' => 'Tottori',
            '島根県' => 'Matsue',
            '岡山県' => 'Okayama',
            '広島県' => 'Hiroshima',
            '山口県' => 'Yamaguchi',
            '徳島県' => 'Tokushima',
            '香川県' => 'Takamatsu',
            '愛媛県' => 'Matsuyama',
            '高知県' => 'Kochi',
            '福岡県' => 'Fukuoka',
            '佐賀県' => 'Saga',
            '長崎県' => 'Nagasaki',
            '熊本県' => 'Kumamoto',
            '大分県' => 'Oita',
            '宮崎県' => 'Miyazaki',
            '鹿児島県' => 'Kagoshima',
            '沖縄県' => 'Naha',
        ];

        return $prefectureMap[$prefecture] ?? 'Tokyo';
    }
}