<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                くしゃみ統計
            </h2>
        </div>
        <p class="text-sm text-gray-800 mt-1">あなたのくしゃみパターンを分析</p>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F8F8] min-h-screen">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- トップサマリーカード群 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="dashboard-card p-5 bg-gradient-to-r from-purple-600 to-pink-500 text-white flex flex-col justify-between">
                    <p class="text-sm opacity-90 mb-1">総くしゃみ回数</p>
                    <p class="text-4xl font-extrabold">{{ $totalSneezeCount ?? 28 }}</p>
                    <p class="text-xs opacity-80 mt-2">6日間の記録</p>
                </div>
                <div class="dashboard-card p-5 bg-gradient-to-r from-blue-500 to-cyan-400 text-white flex flex-col justify-between">
                    <p class="text-sm opacity-90 mb-1">平均辛さレベル</p>
                    <p class="text-4xl font-extrabold">{{ number_format($averageStrength ?? 3.7, 1) }}</p>
                    <p class="text-xs opacity-80 mt-2">5段階中</p>
                </div>
                <div class="dashboard-card p-5 bg-gradient-to-r from-red-500 to-orange-400 text-white flex flex-col justify-between">
                    <p class="text-sm opacity-90 mb-1">最多時間帯</p>
                    <p class="text-4xl font-extrabold">{{ $mostFrequentTime ?? '12-18' }}</p>
                    <p class="text-xs opacity-80 mt-2">くしゃみが多い時間帯</p>
                </div>
            </div>

            {{-- 時間帯別くしゃみ分析 --}}
            <div class="dashboard-card p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">時間帯別くしゃみ分析</h3>
                <p class="text-sm text-gray-600 mb-6">1日の中でくしゃみが多い時間帯</p>

                @php
                    $timeSlots = [
                        ['label' => '00-06時', 'count' => 5, 'color' => 'bg-gray-400'],
                        ['label' => '06-12時', 'count' => 10, 'color' => 'bg-blue-400'],
                        ['label' => '12-18時', 'count' => 15, 'color' => 'bg-purple-500'],
                        ['label' => '18-24時', 'count' => 8, 'color' => 'bg-pink-500'],
                    ];
                    $maxCount = max(array_column($timeSlots, 'count'));
                @endphp

                <div class="space-y-4">
                    @foreach($timeSlots as $slot)
                        <div class="flex items-center">
                            <div class="w-20 text-sm text-gray-700">{{ $slot['label'] }}</div>
                            <div class="flex-grow bg-gray-200 rounded-full h-3 ml-4 relative">
                                <div class="{{ $slot['color'] }} h-full rounded-full" style="width: {{ ($slot['count'] / $maxCount) * 100 }}%;"></div>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-800">{{ $slot['count'] }}回</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 曜日別くしゃみ分析 --}}
            <div class="dashboard-card p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">曜日別くしゃみ分析</h3>
                <p class="text-sm text-gray-600 mb-6">曜日ごとのくしゃみ傾向</p>

                @php
                    $weeklyCounts = [
                        '日' => 6,
                        '月' => 0,
                        '火' => 0,
                        '水' => 0,
                        '木' => 0,
                        '金' => 0,
                        '土' => 0,
                    ];
                    $maxWeeklyCount = max($weeklyCounts);
                @endphp

                <div class="grid grid-cols-7 gap-2 text-center">
                    @foreach($weeklyCounts as $day => $count)
                        <div class="flex flex-col items-center">
                            <span class="text-sm font-semibold text-gray-700 mb-2">{{ $day }}</span>
                            <div class="w-full relative bg-gray-200 rounded-lg overflow-hidden" style="height: 60px;">
                                <div class="absolute bottom-0 left-0 w-full {{ $count > 0 ? 'bg-green-500' : 'bg-gray-300' }} rounded-b-lg" style="height: {{ ($count / ($maxWeeklyCount > 0 ? $maxWeeklyCount : 1)) * 100 }}%;"></div>
                                <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-lg font-bold text-white z-10">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- あなたのくしゃみパターン --}}
            <div class="bg-yellow-50 text-yellow-800 p-6 rounded-2xl shadow-sm">
                <h4 class="font-bold mb-2">あなたのくしゃみパターン</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>あなたのくしゃみは**午前中**が多い傾向です。20時前後が特に注意が必要です。</li>
                    <li>平日より**週末**にくしゃみが多く、特に日曜日のくしゃみが多いようです。外出時の準備を心がけましょう。</li>
                    <li>データを継続的に記録することで、より正確なパターンが見えてきます。くしゃみを記録し続けましょう！</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>