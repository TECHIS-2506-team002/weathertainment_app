<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\SneezeLog;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (!$user) {
            // 未ログインの場合はログインページへリダイレクトするなど、適切な処理
            // 現状ではmiddleware('auth')で保護されているので、この分岐は不要かもしれない
            // しかし、念のためダミーデータを渡しておく
            $totalSneezeCount = 0;
            $averageStrength = 0;
            $mostFrequentTime = 'N/A';
            $timeSlots = [];
            $weeklyCounts = [];
            $sneezePatternComment = 'ログインすると、あなたのくしゃみパターンを分析できます。';
            return view('sneeze.record', compact('totalSneezeCount', 'averageStrength', 'mostFrequentTime', 'timeSlots', 'weeklyCounts', 'sneezePatternComment'));
        }

        // --- サマリーカード用のデータ計算 ---
        $totalSneezeCount = SneezeLog::where('user_id', $user->id)->sum('count');
        $averageStrength = SneezeLog::where('user_id', $user->id)->avg('level') ?? 0;

        // 時間帯別の生データを取得
        $hourlyCounts = SneezeLog::where('user_id', $user->id)
            ->selectRaw('HOUR(created_at) as hour, sum(count) as total_count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // 時間帯別を4つのスロットに集計
        $timeSlotsData = [
            '00-06' => 0, // 深夜
            '06-12' => 0, // 午前中
            '12-18' => 0, // 午後
            '18-24' => 0  // 夕方〜夜
        ];

        foreach ($hourlyCounts as $entry) {
            $hour = $entry->hour;
            if ($hour >= 0 && $hour < 6) $timeSlotsData['00-06'] += $entry->total_count;
            elseif ($hour >= 6 && $hour < 12) $timeSlotsData['06-12'] += $entry->total_count;
            elseif ($hour >= 12 && $hour < 18) $timeSlotsData['12-18'] += $entry->total_count;
            else $timeSlotsData['18-24'] += $entry->total_count;
        }

        $mostFrequentTime = $this->getMostFrequentTime($timeSlotsData);

        // 時間帯別グラフ表示用データ
        $timeSlots = [
            ['label' => '00-06時', 'count' => $timeSlotsData['00-06'], 'color' => 'bg-gray-400'],
            ['label' => '06-12時', 'count' => $timeSlotsData['06-12'], 'color' => 'bg-blue-400'],
            ['label' => '12-18時', 'count' => $timeSlotsData['12-18'], 'color' => 'bg-purple-500'],
            ['label' => '18-24時', 'count' => $timeSlotsData['18-24'], 'color' => 'bg-pink-500'],
        ];

        // --- 曜日別データ計算 ---
        $dayOfWeekCounts = SneezeLog::where('user_id', $user->id)
            ->selectRaw('DAYOFWEEK(created_at) as day_of_week, sum(count) as total_count')
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();

        // MySQLのDAYOFWEEKは1=日曜日, 2=月曜日...
        $weeklyCounts = [
            '日' => 0,
            '月' => 0,
            '火' => 0,
            '水' => 0,
            '木' => 0,
            '金' => 0,
            '土' => 0,
        ];
        $dayMap = [1 => '日', 2 => '月', 3 => '火', 4 => '水', 5 => '木', 6 => '金', 7 => '土'];

        foreach ($dayOfWeekCounts as $entry) {
            $dayLabel = $dayMap[$entry->day_of_week] ?? null;
            if ($dayLabel) {
                $weeklyCounts[$dayLabel] = $entry->total_count; 
            }
        }
        // --- あなたのくしゃみパターンコメント生成 ---
        $sneezePatternComment = $this->generateSneezePatternComment($timeSlotsData, $weeklyCounts, $totalSneezeCount);

        return view('sneeze.record', compact(
            'totalSneezeCount',
            'averageStrength',
            'mostFrequentTime',
            'timeSlots',
            'weeklyCounts',
            'sneezePatternComment'
        ));
    }

    /**
     * 最もくしゃみが多い時間帯を取得する
     */
    private function getMostFrequentTime(array $timeSlotsData): string
    {
        if (empty($timeSlotsData)) {
            return 'N/A';
        }
        $maxCount = 0;
        $mostFrequent = 'N/A';
        foreach ($timeSlotsData as $label => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $mostFrequent = $label;
            }
        }
        return $mostFrequent . '時'; // '00-06時' のような形式にする
    }

    /**
     * くしゃみパターン分析コメントを生成する
     */
    private function generateSneezePatternComment(array $timeSlotsData, array $weeklyCounts, int $totalCount): string
    {
        if ($totalCount < 5) { // ある程度のデータがないと分析できない
            return 'まだデータが少ないため、あなたのくしゃみパターンを詳細に分析できません。くしゃみを記録し続けると、より正確な傾向が分かります！';
        }

        $comments = [];

        // 時間帯分析
        $morningCount = $timeSlotsData['06-12'] + $timeSlotsData['00-06'];
        $afternoonCount = $timeSlotsData['12-18'] + $timeSlotsData['18-24'];

        $mostFrequentTimeSlotKey = array_search(max($timeSlotsData), $timeSlotsData);
        if ($mostFrequentTimeSlotKey === '00-06' || $mostFrequentTimeSlotKey === '06-12') {
            $comments[] = 'あなたのくしゃみは**午前中**に多い傾向です。';
        } elseif ($mostFrequentTimeSlotKey === '12-18') {
            $comments[] = 'あなたのくしゃみは**日中（午後）**に多い傾向です。';
        } elseif ($mostFrequentTimeSlotKey === '18-24') {
            $comments[] = 'あなたのくしゃみは**夕方から夜**にかけて多い傾向です。';
        }

        // 曜日分析
        $weekdayCount = $weeklyCounts['月'] + $weeklyCounts['火'] + $weeklyCounts['水'] + $weeklyCounts['木'] + $weeklyCounts['金'];
        $weekendCount = $weeklyCounts['土'] + $weeklyCounts['日'];

        if ($weekendCount > $weekdayCount * 1.5 && $weekendCount > 0) { // 週末が平日の1.5倍以上
            $comments[] = '特に**週末**にくしゃみが多いようです。外出時の活動に注意が必要かもしれません。';
        } elseif ($weekdayCount > $weekendCount * 1.5 && $weekdayCount > 0) {
            $comments[] = '**平日**にくしゃみが多い傾向です。職場の環境や通勤経路に原因がある可能性も？';
        }

        if (empty($comments)) {
            return 'まだ特定のくしゃみパターンは見られません。さらにデータを記録し続けましょう！';
        }

        return implode(' ', $comments) . ' データを継続的に記録することで、より正確なパターンが見えてきます。くしゃみを記録し続けましょう！';
    }
}
