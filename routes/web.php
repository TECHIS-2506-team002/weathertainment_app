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

// --- エントリーページ ---
Route::get('/', function () {
    return view('entry'); // resources/views/entry.blade.php を表示
})->name('entry');

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
