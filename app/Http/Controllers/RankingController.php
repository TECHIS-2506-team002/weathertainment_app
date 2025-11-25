<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\SneezeLog; // SneezeLogモデルも使うのでインポート
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // 小林追加

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::now('Asia/Tokyo')->toDateString();
        $currentTab = $request->query('tab', 'national');

        // --- 全国ランキング関連のデータを取得 ---
        $nationalRankings = Ranking::where('type', 'sneeze_count')
            ->where('ranking_date', $today)
            ->orderBy('rank', 'asc')
            ->get();

        $worstSneezePrefecture = $nationalRankings->first(); // 1位のデータを取得

        // --- 個人ランキング関連（小林担当部分・修正版） ---
        
        // 1. 今日の個人ランキングTOP10を取得（くしゃみ回数の合計順）
        $personalRankings = SneezeLog::whereDate('created_at', $today)
            ->select('user_id', DB::raw('SUM(count) as total_count'), DB::raw('AVG(level) as avg_level'))
            ->groupBy('user_id')
            ->orderBy('total_count', 'desc') // 回数が多い順
            ->with('user') // ユーザー名を表示するためにリレーションをロード
            ->take(10) // 上位10名を表示
            ->get();

        // 2. ログインユーザー自身の順位と成績を計算
        $myRanking = null;
        if (Auth::check()) {
            $user = Auth::user();
            
            // 自分の今日の合計と平均を取得
            $myStats = SneezeLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->selectRaw('SUM(count) as total_count, AVG(level) as avg_level')
                ->first();

            // データがある場合のみ順位を計算
            if ($myStats && $myStats->total_count > 0) {
                // 自分よりくしゃみ回数が多いユーザーの人数を数える（+1が自分の順位）
                // ※サブクエリを使って「合計回数が自分より多いユーザー数」を取得
                $rank = SneezeLog::whereDate('created_at', $today)
                    ->groupBy('user_id')
                    ->selectRaw('SUM(count) as total_count')
                    ->having('total_count', '>', $myStats->total_count)
                    ->get()
                    ->count() + 1;

                $myRanking = [
                    'rank' => $rank,
                    'name' => $user->name,
                    'sneeze_count' => $myStats->total_count,
                    'avg_level' => round($myStats->avg_level, 1), // 小数点第1位まで
                ];
            }
        }

        // ビューに渡す変数を追加 ($myRanking)
        return view('ranking.index', compact(
            'currentTab',
            'nationalRankings',
            'worstSneezePrefecture',
            'personalRankings', // TOP10リスト
            'myRanking'         // 自分の順位データ
        ));
    }
}
