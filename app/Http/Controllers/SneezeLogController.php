<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SneezeLog; // SneezeLogモデルをインポートする
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse; // リダイレクトレスポンスの型宣言用

class SneezeLogController extends Controller
{
    /**
     * 新しいくしゃみ記録を保存する
     */
    public function store(Request $request): RedirectResponse
    {
        // 未ログインユーザーは記録できないようにする
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'ログインしてくしゃみを記録してください。');
        }

        // フォームからの入力をバリデーション（検証）
        $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:5'],
            'count' => ['required', 'integer', 'min:1'],
            'memo' => ['nullable', 'string', 'max:500'],
        ]);

        // くしゃみログをデータベースに保存
        SneezeLog::create([
            'user_id' => Auth::id(),
            'level' => $request->level,
            'count' => $request->count,
            'memo' => $request->memo,
            // 'prefecture' は、WeatherControllerから渡すか、後でGeoIPで取得
            'prefecture' => Auth::user()->prefecture ?? null, // ユーザーの登録都道府県を初期値に
        ]);

        return redirect()->back()->with('success', 'くしゃみを記録しました！');
    }
}
