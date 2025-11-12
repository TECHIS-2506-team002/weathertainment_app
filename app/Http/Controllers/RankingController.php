<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// 必要に応じてモデルをインポート

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'national'); // URLパラメータからタブを取得、デフォルトは national

        // --- 全国くしゃみワースト1位カード用のダミーデータ ---
        // 実際はデータベースから取得
        $worstSneezePrefectureRank = 1;
        $worstSneezePrefectureName = '北海道'; // 例: 最もくしゃみが多い都道府県
        $worstSneezeCount = 6; // 例: その都道府県のくしゃみ回数
        $worstSneezeAvgLevel = 4.2; // 例: その都道府県の平均強さレベル

        // --- 全国ランキング表示用のダミーデータ (くしゃみ回数が多い順) ---
        // 実際はデータベースから取得
        $nationalSneezeRankings = [
            ['rank' => 1, 'prefecture' => '北海道', 'sneeze_count' => 6, 'avg_level' => 4],
            ['rank' => 2, 'prefecture' => '東京都', 'sneeze_count' => 5, 'avg_level' => 3.8],
            ['rank' => 3, 'prefecture' => '大阪府', 'sneeze_count' => 5, 'avg_level' => 3.5],
            ['rank' => 4, 'prefecture' => '福岡県', 'sneeze_count' => 4, 'avg_level' => 3.0],
            ['rank' => 5, 'prefecture' => '愛知県', 'sneeze_count' => 3, 'avg_level' => 2.5],
            // ... さらに多くのデータ
        ];

        // --- 個人ランキング表示用のダミーデータ ---
        // 実際は認証ユーザーのデータから取得
        $personalRankings = [
            'rank' => 1, // あなたの順位（もしあれば）
            'sneeze_count' => 6, // あなたのくしゃみ回数
            'avg_level' => 4, // あなたの平均強さレベル
        ];


        return view('ranking.index', compact(
            'currentTab',
            'worstSneezePrefectureRank',
            'worstSneezePrefectureName',
            'worstSneezeCount',
            'worstSneezeAvgLevel',
            'nationalSneezeRankings',
            'personalRankings'
        ));
    }

    public function update(Request $request)
    {
        // ランキング更新ロジック
        // 例: 外部APIからデータを取得し、DBに保存するなど
        // 今回の画像では更新ボタンは削除したので、このメソッドは使わないかもしれません。
        // 必要に応じて実装してください。
        return redirect()->route('ranking')->with('success', 'ランキングデータが更新されました！');
    }
}