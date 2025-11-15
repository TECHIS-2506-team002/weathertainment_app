<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ranking;
use App\Models\SneezeLog;
use Carbon\Carbon;

class UpdateRankingCommand extends Command
{
    protected $signature = 'ranking:update';
    protected $description = 'sneeze_logsテーブルを集計し、都道府県別のくしゃみ回数ランキングを作成します。';

    public function handle()
    {
        $this->info('くしゃみログを集計し、ランキングの更新を開始します...');
        $today = Carbon::today();

        // STEP 1: 今日のくしゃみログを持つユーザーとその都道府県を取得
        $logs = SneezeLog::with('user:id,prefecture')
            ->whereDate('created_at', $today)
            ->whereHas('user', fn($query) => $query->whereNotNull('prefecture'))
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('本日のくしゃみログがありません。ランキングは更新されませんでした。');
            // 古いデータを削除しておく
            Ranking::where('ranking_date', $today)->delete();
            return Command::SUCCESS;
        }

        // STEP 2: 都道府県ごとに統計情報を集計
        $statsByPrefecture = $logs->groupBy('user.prefecture')
            ->map(function ($logsInPrefecture) {
                $totalCount = $logsInPrefecture->sum('count');
                $weightedLevelSum = $logsInPrefecture->sum(fn($log) => $log->level * $log->count);
                $averageLevel = ($totalCount > 0) ? $weightedLevelSum / $totalCount : 0;

                return [
                    'prefecture' => $logsInPrefecture->first()->user->prefecture,
                    'total_count' => $totalCount,
                    'average_level' => round($averageLevel, 1),
                ];
            });

        // STEP 3: くしゃみ回数が多い順にソート
        $rankingData = $statsByPrefecture->sortByDesc('total_count')->values();

        // STEP 4: ランキングをDBに保存
        $this->saveRanking('sneeze_count', $rankingData, $today);

        $this->info('全ランキングの更新が完了しました！');
        return Command::SUCCESS;
    }

    private function saveRanking(string $type, $rankingData, Carbon $date): void
    {
        Ranking::where('type', $type)->where('ranking_date', $date)->delete();

        foreach ($rankingData as $index => $data) {
            Ranking::create([
                'type' => $type,
                'ranking_date' => $date,
                'prefecture' => $data['prefecture'],
                'total_count' => $data['total_count'],
                'average_level' => $data['average_level'],
                'rank' => $index + 1,
            ]);
        }
    }
}
