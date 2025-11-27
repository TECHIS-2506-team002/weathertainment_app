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
        // 現在ログインしているユーザーを取得
        $user = Auth::user();

        // === STEP 1: 表示する都市名を決定するロジック ===
        // 県庁所在地リストを読み込む
        // Laravelのconfigディレクトリに配置することを想定
        $capitals = config('capitals.prefectural_capitals');

        // まず、地域選択フォームからの入力を最優先でチェック
        $cityFromRequest = $request->input('prefecture');

        if ($cityFromRequest) {
            // フォームからの入力があれば、それを$cityとする
            $city = $cityFromRequest;
            // 県庁所在地名を取得
            $capitalCity = $capitals[$city] ?? $city;
        } elseif ($user && $user->prefecture) { // $userがnullでないことを確認
            // フォーム入力がなく、ログインしていて、かつ都道府県が登録されていれば、それを使う
            $city = $user->prefecture;
            // 県庁所在地名を取得
            $capitalCity = $capitals[$city] ?? $city;
        } else {
            // 上記のいずれでもなければ、デフォルトで'東京都'を使う
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
        $sneezeRateCalculationMethod = '天気情報のみ'; // 計算方法の初期値

        if ($weatherData) {
            // 天気情報から基本のくしゃみ確率を算出
            $baseSneezeRate = $this->weatherService->calculateSneezeRateFromWeather($weatherData); // WeatherServiceに新しく定義するメソッド

            if ($hasNoseType) {
                // 鼻タイプと天気情報を組み合わせてくしゃみ確率を算出
                $result = $this->weatherService->calculatePersonalSneezeRate($baseSneezeRate, $noseTypeInfo['type'], $weatherData, $user); // WeatherServiceに新しく定義するメソッド
                $sneezeRate = $result['rate'];
                $sneezeReliability = $result['reliability'];
                $sneezeRateCalculationMethod = '天気情報＋体質情報';
            } else {
                // 体質情報が未設定の場合は、天気情報のみで算出
                $sneezeRate = $baseSneezeRate;
                $sneezeReliability = $this->weatherService->calculateReliabilityFromWeather($weatherData); // WeatherServiceに新しく定義するメソッド
                $sneezeRateCalculationMethod = '天気情報のみ';
            }
        } else {
            // 天気情報が取得できない場合
            $sneezeRate = 'N/A';
            $sneezeReliability = 0;
            $sneezeRateCalculationMethod = 'データなし';
        }


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

        // ログイン状態に応じて、表示するビューを切り替える
        if ($user) {
            return view('dashboard', $viewData);
        } else {
            return view('home', $viewData);
        }
    }
}