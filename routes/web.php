<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\ProfileController;

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

// --- ランキングページ ---
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::post('/ranking/update', [RankingController::class, 'update'])->name('ranking.update');

// --- 認証関連 ---
// デモログイン機能
Route::get('/demo-login', [DemoLoginController::class, 'login'])->name('demo.login');

// ユーザープロファイル関連（Breezeが生成）
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breezeの認証ルート（login, register, logoutなど）の読み込み
require __DIR__ . '/auth.php';