<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $selectedType = $request->input('type', 'sneeze');

        $rankings = [
            'sneeze' => $this->getTodaysRanking('sneeze', $today),
            'fringe_collapse' => $this->getTodaysRanking('fringe_collapse', $today),
            'laundry_mold' => $this->getTodaysRanking('laundry_mold', $today),
        ];

        $stats = $this->calculateStats($rankings[$selectedType], $today);

        // フォームから選択された都道府県を取得
        $chartPrefecture = $request->input('chart_prefecture');
        // 取得した値を、正しくメソッドに渡す
        $chartData = $this->getWeeklyChartDataForUser($selectedType, $chartPrefecture);

        // dd($selectedType, $chartPrefecture, $chartData); // デバッグが必要な場合はこちらを有効化

        return view('ranking.index', compact('rankings', 'selectedType', 'stats', 'chartData'));
    }

    public function update(): RedirectResponse
    {
        try {
            Artisan::call('ranking:update');
            return redirect()->back()->with('success', 'ランキングを更新しました！');
        } catch (\Exception $e) {
            Log::error('Ranking update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'ランキングの更新に失敗しました。');
        }
    }

    private function getTodaysRanking(string $type, Carbon $date)
    {
        return Ranking::where('type', $type)
            ->where('ranking_date', $date)
            ->orderBy('rank', 'asc')
            ->get();
    }

    private function calculateStats($rankings, Carbon $date): array
    {
        if ($rankings->isEmpty()) {
            return [
                'updateDate' => $date->format('Y/m/d'),
                'prefectureCount' => 0,
                'averageScore' => 0,
            ];
        }
        return [
            'updateDate' => $date->format('Y/m/d'),
            'prefectureCount' => $rankings->count(),
            'averageScore' => round($rankings->avg('score'), 1),
        ];
    }

    /**
     * ユーザーの居住地の週間推移グラフ用データを取得する
     */
    // ★★★ メソッド定義を修正し、第2引数を受け取れるようにする ★★★
    private function getWeeklyChartDataForUser(string $type, ?string $prefectureFromForm): array
    {
        // フォームからの値があれば最優先し、なければフォールバック処理を行う
        $targetPrefecture = $prefectureFromForm ?? (Auth::check() ? Auth::user()->prefecture : '東京都');

        $scoresByDate = Ranking::where('type', $type)
            ->where('prefecture', $targetPrefecture)
            ->where('ranking_date', '>=', Carbon::today()->subDays(6))
            ->orderBy('ranking_date', 'asc')
            ->pluck('score', 'ranking_date');

        $labels = [];
        $scores = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->toDateString();

            $labels[] = $date->format('n/j');
            $scores[] = $scoresByDate[$dateString] ?? 0;
        }

        return [
            'labels' => $labels,
            'scores' => $scores,
            'label' => $this->getChartLabel($type),
            'prefecture' => $targetPrefecture,
        ];
    }

    /**
     * 指数タイプに応じたグラフのラベル名を取得する
     */
    private function getChartLabel(string $type): string
    {
        return match ($type) {
            'fringe_collapse' => '前髪崩壊率',
            'sneeze' => 'くしゃみ確率',
            'laundry_mold' => '洗濯物カビリスク',
            default => 'スコア',
        };
    }
}
