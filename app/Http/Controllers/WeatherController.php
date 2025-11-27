<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class WeatherController extends Controller
{
    protected $weatherService;

    // コンストラクタでWeatherServiceをインジェクション
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // === STEP 1: 表示する都市名を決定するロジック ===
        // 県庁所在地リストを読み込む
        // Laravelのconfigディレクトリに配置することを想定
        $capitals = config('capitals.prefectural_capitals');

        // まず、地域選択フォームからの入力を最優先でチェック
        $cityFromRequest = $request->input('prefecture');
        if ($cityFromRequest) {
            $city = $cityFromRequest;
            // 県庁所在地名を取得
            $capitalCity = $capitals[$city] ?? $city;
        } elseif ($user && $user->prefecture) { // $userがnullでないことを確認
            // フォーム入力がなく、ログインしていて、かつ都道府県が登録されていれば、それを使う
            $city = $user->prefecture;
            // 県庁所在地名を取得
            $capitalCity = $capitals[$city] ?? $city;
        } else {
            $city = '東京都';
            // 未ログイン時は、天気取得都市を「新宿」に固定する
            $capitalCity = '新宿区';
        }

        // === STEP 2: 天気情報を取得 ===
        // 天気情報を取得する都市名には、県庁所在地名を使用する
        $weatherData = $this->weatherService->getCurrentWeather($capitalCity);

        // --- ビューに渡すための共通データをまとめる ---
        $viewData = [];

        // ユーザーの鼻タイプ情報を取得
        $noseTypeInfo = ['type' => '未設定タイプ', 'icon' => '❓', 'description' => '体質情報を設定すると、あなたの鼻タイプが表示されます。'];
        $hasNoseType = false; // 鼻タイプが設定されているかどうかのフラグ

        if ($user) {
            $noseTypeInfo = $user->getNoseType();
            // 未設定タイプでなければ、鼻タイプが設定されていると判断
            if ($noseTypeInfo['type'] !== '未設定タイプ') {
                $hasNoseType = true;
            }
        }

        // 鼻タイプが設定されているかどうかの注釈テキスト
        $sneezeRateNote = '';
        if (!$hasNoseType) {
            $sneezeRateNote = '体質情報を設定し、鼻タイプが診断されることでさらに正確なくしゃみ確率が割り出されます。';
        }


        // くしゃみ確率と信頼度の算出
        $sneezeRate = 'N/A';
        $sneezeReliability = 0;
        $weatherData = $this->weatherService->getCurrentWeather($city);
        $comprehensiveData = null; // 初期化

        if ($weatherData) {
            $lat = $weatherData['coord']['lat'];
            $lon = $weatherData['coord']['lon'];
            $airPollutionData = $this->weatherService->getAirPollution($lat, $lon);
            $oneCallData = $this->weatherService->getOneCallData($lat, $lon);
            $comprehensiveData = array_merge($weatherData, $airPollutionData ?? [], $oneCallData ?? []);
        }

        // STEP 3: くしゃみ確率の算出
        if ($comprehensiveData) {
            $baseSneezeRate = $this->weatherService->calculateSneezeRateFromWeather($comprehensiveData);
            if ($user && $user->getNoseType()['type'] !== '未設定タイプ') {
                $result = $this->weatherService->calculatePersonalSneezeRate($baseSneezeRate, $user, $comprehensiveData);
                $sneezeRate = $result['rate'];
                $sneezeReliability = $result['reliability'];
            } else {
                $sneezeRate = $baseSneezeRate;
                $sneezeReliability = $this->weatherService->calculateReliabilityFromWeather($comprehensiveData);
            }
        }

        // ★★★ ここから、不足していた変数の準備ロジック ★★★

        // $viewDataにユーザーの鼻タイプ情報と計算結果を追加
        $viewData['user'] = $user; // デバッグ情報用にユーザーオブジェクトを渡す
        $viewData['userNoseType'] = $noseTypeInfo['type'];
        $viewData['userNoseTypeIcon'] = $noseTypeInfo['icon'];
        $viewData['userNoseTypeDescription'] = $noseTypeInfo['description'];
        $viewData['hasNoseType'] = $hasNoseType; // 鼻タイプが設定されているかどうかのフラグ
        $viewData['sneezeRateNote'] = $sneezeRateNote; // 注釈テキスト


        $viewData['sneezeRate'] = $sneezeRate;
        $viewData['sneezeReliability'] = $sneezeReliability;
        $viewData['sneezeRateCalculationMethod'] = $sneezeRateCalculationMethod; // デバッグ用


        if ($weatherData) {
            $viewData['weatherData'] = $weatherData;
            $viewData['selectedCity'] = $city;
            $viewData['capitalCity'] = $capitalCity;

            // X（旧Twitter）シェアテキストの生成
            // 個人の確率と鼻タイプをメインとする
            $shareText = "私の今日のくしゃみ確率は【{$sneezeRate}%】でした！";
            if ($hasNoseType) {
                $shareText .= "鼻タイプは「{$noseTypeInfo['type']}」です。";
            } else {
                $shareText .= "体質情報を設定すると、もっと正確な確率がわかるかも？";
            }
            $shareText .= " #くしゃみアプリ #鼻ムズバスターズ";

            $appUrl = url('/');

            $viewData['twitterShareUrl'] = "https://twitter.com/intent/tweet?" . http_build_query([
                'text' => $shareText,
                'url' => $appUrl
            ]);

            // OGP用のデータを追加
            $viewData['title'] = "今日のくしゃみ確率 {$sneezeRate}%";
            $viewData['description'] = $shareText;
            $viewData['ogImage'] = secure_asset('images/ogp-default.png');

        } else {
            // 天気取得失敗時のデータ
            $viewData['weatherData'] = null;
            $viewData['selectedCity'] = $city;
            $viewData['capitalCity'] = $capitalCity; // 失敗時も選択された都市名は渡す

            // 天気情報が取得できなかった場合でも、くしゃみ確率と信頼度をデフォルト値で渡す
            // ここでのくしゃみ確率は上記で設定済みだが、N/Aの場合はここで再度設定
            $viewData['sneezeRate'] = 'N/A';
            $viewData['sneezeReliability'] = 0;
            $viewData['twitterShareUrl'] = "https://twitter.com/intent/tweet?" . http_build_query([
                'text' => "くしゃみアプリであなたの鼻タイプと今日のくしゃみ確率をチェック！ #くしゃみアプリ",
                'url' => url('/')
            ]);
            $viewData['title'] = "くしゃみアプリ";
            $viewData['description'] = "くしゃみアプリであなたの鼻タイプと今日のくしゃみ確率をチェック！";
            $viewData['ogImage'] = asset('images/ogp-default.png');
        }

        // シェアテキストの生成
        $shareText = "私の今日のくしゃみ確率は【{$sneezeRate}%】でした！";
        if ($hasNoseType) {
            $shareText .= " 鼻タイプは「{$noseTypeInfo['type']}」です。";
        }
        $shareText .= " #鼻ムズバスターズ";
        $appUrl = url('/');
        $twitterShareUrl = "https://twitter.com/intent/tweet?" . http_build_query([
            'text' => $shareText,
            'url' => $appUrl
        ]);

        // OGP用のデータ
        $ogpTitle = "今日のくしゃみ確率 {$sneezeRate}%";
        $ogpDescription = $shareText;

        // ★★★ ここまで ★★★

        // STEP 4: ビューに渡すデータを全てまとめる
        $viewData = [
            'user' => $user,
            'weatherData' => $weatherData,
            'selectedCity' => $city,
            'sneezeRate' => $sneezeRate,
            'sneezeReliability' => $sneezeReliability,
            'userNoseType' => $noseTypeInfo['type'],
            'userNoseTypeIcon' => $noseTypeInfo['icon'],
            'userNoseTypeDescription' => $noseTypeInfo['description'],
            'hasNoseType' => $hasNoseType,
            'sneezeRateNote' => $sneezeRateNote,
            'twitterShareUrl' => $twitterShareUrl,
            'title' => $ogpTitle,
            'description' => $ogpDescription,
            'ogImage' => asset('images/ogp-default.png'),
        ];

        // STEP 5: ビューを返す
        if ($user) {
            return view('dashboard', $viewData);
        } else {
            return view('home', $viewData);
        }
    }
}
