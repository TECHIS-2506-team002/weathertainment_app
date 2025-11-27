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
        if (!$city) {
            $city = '東京都';
        }
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
        return null;
    }

    /**
     * 指定された緯度経度の大気汚染情報を取得する
     */
    public function getAirPollution(float $lat, float $lon): ?array
    {
        $apiKey = config('services.openweathermap.key');
        if (! $apiKey) {
            return null;
        }

        $response = Http::get('https://api.openweathermap.org/data/2.5/air_pollution', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['list'][0])) {
                return [
                    'aqi' => $data['list'][0]['main']['aqi'],
                    'components' => $data['list'][0]['components'],
                ];
            }
        }
        return null;
    }

    /**
     * 指定された緯度経度のOne Call APIデータを取得する
     */
    public function getOneCallData(float $lat, float $lon): ?array
    {
        $apiKey = config('services.openweathermap.key');
        if (! $apiKey) {
            return null;
        }

        $response = Http::get('https://api.openweathermap.org/data/3.0/onecall', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
            'units' => 'metric',
            'exclude' => 'current,minutely,hourly,alerts',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['daily'][0])) {
                return [
                    'daily_weather' => $data['daily'][0],
                ];
            }
        }
        return null;
    }

    // getCurrentWeather, getAirPollution, getOneCallData の3メソッドは、変更なし

    // ★★★ ここから下が、完全に新しいロジックになります ★★★

    /**
     * 6つのサブスコアモデルに基づき、基本くしゃみ確率を算出する
     *
     * @param array $data 3つのAPIから集約された気象データ
     * @return int 0-100
     */
    public function calculateSneezeRateFromWeather(array $data): int
    {
        $pollenProxyScore = $this->calculatePollenProxyScore($data); // 0-20点
        $drynessScore     = $this->calculateDrynessScore($data);     // 0-20点
        $windScore        = $this->calculateWindScore($data);        // 0-20点
        $pressureScore    = $this->calculatePressureScore($data);    // 0-15点
        $pollutionScore   = $this->calculatePollutionScore($data);   // 0-20点
        $tempGapScore     = $this->calculateTempGapScore($data);     // 0-15点

        $totalScore = $pollenProxyScore + $drynessScore + $windScore + $pressureScore + $pollutionScore + $tempGapScore;

        return min(100, $totalScore); // 合計が100を超えた場合でも100に丸める
    }

    // --- 6つのサブスコア計算メソッド (全てprivate) ---

    private function calculatePollenProxyScore(array $data): int
    {
        $score = 0;
        if (($data['weather'][0]['main'] ?? '') === 'Clear') {
            $score += 5;
        }
        if (($data['main']['humidity'] ?? 100) < 40) {
            $score += 5;
        }
        if (($data['wind']['speed'] ?? 0) >= 4) {
            $score += 10;
        }
        return $score;
    }

    private function calculateDrynessScore(array $data): int
    {
        $humidity = $data['main']['humidity'] ?? 100;
        if ($humidity < 20) return 20;
        if ($humidity < 40) return 10;
        return 0;
    }

    private function calculateWindScore(array $data): int
    {
        $windSpeed = $data['wind']['speed'] ?? 0;
        if ($windSpeed >= 8) return 20;
        if ($windSpeed >= 5) return 10;
        return 0;
    }

    private function calculatePressureScore(array $data): int
    {
        $pressure = $data['main']['pressure'] ?? 1015;
        if ($pressure < 1010) return 15;
        if ($pressure < 1015) return 8;
        return 0;
    }

    private function calculatePollutionScore(array $data): int
    {
        $aqi = $data['aqi'] ?? 1; // getAirPollutionから渡されるAQI
        return match ($aqi) {
            1 => 0,
            2 => 5,
            3 => 10,
            4 => 15,
            5 => 20,
            default => 0,
        };
    }

    private function calculateTempGapScore(array $data): int
    {
        $morningTemp = $data['daily_weather']['temp']['morn'] ?? null;
        $dayTemp = $data['daily_weather']['temp']['day'] ?? null;

        if ($morningTemp === null || $dayTemp === null) {
            return 0; // データがなければ0点
        }

        $gap = abs($dayTemp - $morningTemp); // 絶対値で差を計算
        if ($gap >= 15) return 15;
        if ($gap >= 10) return 8;
        return 0;
    }


    // --- 信頼度とパーソナル確率の計算ロジックも刷新 ---

    public function calculatePersonalSneezeRate(int $baseSneezeRate, User $user, array $data): array
    {
        $personalRate = $baseSneezeRate;

        // 感度レベルを補正係数に変換 (1-5 -> 0.8-1.2)
        $allergyMultiplier = 1 + (($user->allergy_sensitivity ?? 3) - 3) * 0.1;
        $tempMultiplier = 1 + (($user->temperature_sensitivity ?? 3) - 3) * 0.1;
        $weatherMultiplier = 1 + (($user->weather_sensitivity ?? 3) - 3) * 0.1;

        // アレルギー感度は、花粉・乾燥・風・汚染スコアに影響
        $personalRate += ($this->calculatePollenProxyScore($data) + $this->calculateDrynessScore($data) + $this->calculateWindScore($data) + $this->calculatePollutionScore($data)) * ($allergyMultiplier - 1);

        // 寒暖差感度は、気温差スコアに影響
        $personalRate += $this->calculateTempGapScore($data) * ($tempMultiplier - 1);

        // 気象病感度は、気圧スコアに影響
        $personalRate += $this->calculatePressureScore($data) * ($weatherMultiplier - 1);

        $reliability = 90; // 体質情報があるので信頼度は高い

        return [
            'rate' => max(0, min(100, (int)round($personalRate))),
            'reliability' => $reliability,
        ];
    }

    public function calculateReliabilityFromWeather(array $data): int
    {
        // 簡易的な信頼度。データがどれだけ揃っているかで判定
        $score = 50;
        if (isset($data['main'])) $score += 10;
        if (isset($data['aqi'])) $score += 10;
        if (isset($data['daily_weather'])) $score += 10;
        return $score;
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
