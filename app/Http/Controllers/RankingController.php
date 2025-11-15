<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\SneezeLog; // SneezeLogモデルも使うのでインポート
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $currentTab = $request->query('tab', 'national');

        // --- 全国ランキング関連のデータを取得 ---
        $nationalRankings = Ranking::where('type', 'sneeze_count')
            ->where('ranking_date', $today)
            ->orderBy('rank', 'asc')
            ->get();

        $worstSneezePrefecture = $nationalRankings->first(); // 1位のデータを取得

        // --- 個人ランキング関連のデータを取得 --- (小林さん担当部分)
        $personalRankings = null;
        if (Auth::check()) {
            $user = Auth::user();
            $userTotalCount = SneezeLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->sum('count');

            $userAverageLevel = SneezeLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->avg('level');

            // 全国ランキングの中から自分の順位を探す
            $userRank = $nationalRankings->firstWhere('prefecture', $user->prefecture);

            $personalRankings = [
                'rank' => $userRank ? $userRank->rank : null,
                'sneeze_count' => $userTotalCount,
                'avg_level' => round($userAverageLevel, 1),
                'prefecture' => $user->prefecture,
            ];
        }

        return view('ranking.index', compact(
            'currentTab',
            'nationalRankings',
            'worstSneezePrefecture',
            'personalRankings'
        ));
    }
}
