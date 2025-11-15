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
                    <p class="text-4xl font-extrabold">{{ $totalSneezeCount }}</p>
                    {{-- 総日数を計算するロジックは後で追加 --}}
                    <p class="text-xs opacity-80 mt-2">（全期間）</p>
                </div>
                <div class="dashboard-card p-5 bg-gradient-to-r from-blue-500 to-cyan-400 text-white flex flex-col justify-between">
                    <p class="text-sm opacity-90 mb-1">平均辛さレベル</p>
                    <p class="text-4xl font-extrabold">{{ number_format($averageStrength, 1) }}</p>
                    <p class="text-xs opacity-80 mt-2">5段階中</p>
                </div>
                <div class="dashboard-card p-5 bg-gradient-to-r from-red-500 to-orange-400 text-white flex flex-col justify-between">
                    <p class="text-sm opacity-90 mb-1">最多時間帯</p>
                    <p class="text-4xl font-extrabold">{{ $mostFrequentTime }}</p>
                    <p class="text-xs opacity-80 mt-2">くしゃみが多い時間帯</p>
                </div>
            </div>

            {{-- 時間帯別くしゃみ分析 --}}
            <div class="dashboard-card p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">時間帯別くしゃみ分析</h3>
                <p class="text-sm text-gray-600 mb-6">1日の中でくしゃみが多い時間帯</p>

                @php
                $maxCount = !empty($timeSlots) ? max(array_column($timeSlots, 'count')) : 1;
                @endphp

                <div class="space-y-4">
                    @forelse($timeSlots as $slot)
                    <div class="flex items-center">
                        <div class="w-20 text-sm text-gray-700">{{ $slot['label'] }}</div>
                        <div class="flex-grow bg-gray-200 rounded-full h-3 ml-4 relative">
                            <div class="{{ $slot['color'] }} h-full rounded-full" style="width: {{ ($slot['count'] / $maxCount) * 100 }}%;"></div>
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-800">{{ $slot['count'] }}回</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">まだ記録がありません。</p>
                    @endforelse
                </div>
            </div>

            {{-- 曜日別くしゃみ分析 --}}
            <div class="dashboard-card p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">曜日別くしゃみ分析</h3>
                <p class="text-sm text-gray-600 mb-6">曜日ごとのくしゃみ傾向</p>

                @php
                $maxWeeklyCount = !empty($weeklyCounts) ? max($weeklyCounts) : 1;
                @endphp

                <div class="grid grid-cols-7 gap-2 text-center">
                    @foreach($weeklyCounts as $day => $count)
                    <div class="flex flex-col items-center">
                        <span class="text-sm font-semibold text-gray-700 mb-2">{{ $day }}</span>
                        <div class="w-full relative bg-gray-200 rounded-lg overflow-hidden" style="height: 60px;">
                            <div class="absolute bottom-0 left-0 w-full {{ $count > 0 ? 'bg-green-500' : 'bg-gray-300' }} rounded-b-lg" style="height: {{ ($count / $maxWeeklyCount) * 100 }}%;"></div>
                            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-lg font-bold text-white z-10">{{ $count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- あなたのくしゃみパターン --}}
            <div class="bg-yellow-50 text-yellow-800 p-6 rounded-2xl shadow-sm">
                <h4 class="font-bold mb-2">あなたのくしゃみパターン</h4>
                {{-- ★★★ 固定テキストを変数に置き換え ★★★ --}}
                <p class="text-sm whitespace-pre-line">{!! e($sneezePatternComment) !!}</p>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- Tailwind CSS JIT Compiler Safelist --}}
{{-- JITコンパイラが動的に生成されるクラス名を認識できない問題への対策。 --}}
{{-- このコメントはブラウザには表示されませんが、ビルド時に以下のクラスがCSSに含まれるようになる。 --}}
{{-- <div class="bg-gray-400 bg-blue-400 bg-purple-500 bg-pink-500"></div> --}}