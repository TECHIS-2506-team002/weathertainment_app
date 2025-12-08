<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SneezeLogController;
use App\Http\Controllers\StatisticsController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- 天気予報ページ ---
// ログイン前（ゲスト）用のトップページ
Route::get('/', [WeatherController::class, 'index'])->name('home');
// ログイン後のダッシュボード（実質的なトップページと同じ）
Route::get('/dashboard', [WeatherController::class, 'index'])->middleware('auth')->name('dashboard');

// --- くしゃみ記録ページ ---
Route::middleware(['auth'])->group(function () {
    // ダッシュボードから「くしゃみを記録」ページへのルート
    Route::get('/sneeze/create', function () {
        return view('sneeze.create');
    })->name('sneeze.create');

    // もしフォーム送信を処理するなら、POSTルートも必要
    Route::post('/sneeze', [SneezeLogController::class, 'store'])->name('sneeze.store');
});

// --- くしゃみ統計ページ ---
Route::middleware(['auth'])->group(function () {
    // ダッシュボードから「くしゃみ統計」ページへのルート
    Route::get('/sneeze/record', [StatisticsController::class, 'index'])->name('sneeze.record');
});

// --- ランキングページ ---
Route::middleware(['auth'])->group(function () {
    // ダッシュボードから「ランキング」ページへのルート
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
    Route::post('/ranking/update', [RankingController::class, 'update'])->name('ranking.update');
});

// --- 認証関連 ---
// デモログイン機能
Route::get('/demo-login', [DemoLoginController::class, 'login'])->name('demo.login');

// ユーザープロファイル関連（Breezeが生成）
Route::middleware('auth')->group(function () {
    // プロフィール編集フォームの表示
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // ユーザー情報（名前、メールアドレス）の更新
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // ユーザーアカウントの削除
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breezeの認証ルート（login, register, logoutなど）の読み込み
require __DIR__ . '/auth.php';

// ↓ここから追加（テスト用）
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

Route::get('/debug-test', function () {
    try {
        // 1. データベース接続テスト
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        $msg = "✅ データベース接続OK (DB名: {$dbName})<br>";

        // 2. ユーザー取得テスト
        $count = \App\Models\User::count();
        $msg .= "✅ ユーザー数取得OK (現在 {$count} 人)<br>";

        // 3. セッション書き込みテスト
        Session::put('test_key', 'test_value');
        $sessionData = Session::get('test_key');
        if ($sessionData === 'test_value') {
            $msg .= "✅ セッション書き込みOK (Driver: " . config('session.driver') . ")<br>";
        } else {
            $msg .= "❌ セッション書き込み失敗<br>";
        }

        // 4. 設定確認
        $msg .= "<hr>現在の設定:<br>";
        $msg .= "DB_HOST: " . config('database.connections.pgsql.host') . "<br>";
        $msg .= "SSL_MODE: " . config('database.connections.pgsql.sslmode') . "<br>";
        $msg .= "LOG_CHANNEL: " . config('logging.default') . "<br>";

        return $msg;

    } catch (\Exception $e) {
        // エラーが起きたらその内容を表示
        return "<h1>❌ エラー発生！</h1><p>" . $e->getMessage() . "</p>";
    }
});