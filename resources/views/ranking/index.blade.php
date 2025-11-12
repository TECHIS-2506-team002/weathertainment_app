<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ランキング
            </h2>
        </div>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F8F8] min-h-screen">
        <div class="max-w-4xl mx-auto space-y-8">

            {{-- 全国くしゃみワースト1位カード (画像に合わせて再構築) --}}
            <div class="dashboard-card p-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-white relative overflow-hidden">
                <div class="flex items-center mb-4">
                    <span class="text-4xl mr-3">🏆</span>
                    <h3 class="text-2xl font-bold">くしゃみワースト1位</h3>
                </div>
                <p class="text-sm opacity-90 mb-6">本日最もくしゃみが多い地域</p>

                <div class="flex items-center justify-around text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-5xl">👑</span>
                        <p class="text-2xl font-extrabold mt-2">{{ $worstSneezePrefectureRank ?? 1 }}</p>
                        <p class="text-sm opacity-90">位 {{ $worstSneezePrefectureName ?? '鼻炎王' }}</p>
                    </div>
                    <div class="border-l border-white/30 h-24 mx-4"></div> {{-- 区切り線 --}}
                    <div>
                        <p class="text-4xl font-extrabold">{{ $worstSneezeCount ?? 6 }}</p>
                        <p class="text-sm opacity-90">くしゃみ回数</p>
                    </div>
                    <div class="border-l border-white/30 h-24 mx-4"></div> {{-- 区切り線 --}}
                    <div>
                        <p class="text-4xl font-extrabold">{{ number_format($worstSneezeAvgLevel ?? 4.2, 1) }}</p>
                        <p class="text-sm opacity-90">平均辛さレベル</p>
                    </div>
                </div>
            </div>

            {{-- ランキングタブ切り替え (全国/個人) --}}
            <div class="dashboard-card p-0">
                <div class="flex border-b border-gray-200">
                    {{-- 全国ランキングタブ --}}
                    <a href="{{ route('ranking', ['tab' => 'national']) }}"
                        class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-lg font-medium
                              {{ ($currentTab ?? 'national') === 'national' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        全国ランキング
                    </a>
                    {{-- 個人ランキングタブ --}}
                    <a href="{{ route('ranking', ['tab' => 'personal']) }}"
                        class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-lg font-medium
                              {{ ($currentTab ?? '') === 'personal' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        個人ランキング
                    </a>
                </div>

                <div class="p-6">
                    @if (($currentTab ?? 'national') === 'national')
                    {{-- 全国ランキング表示 (くしゃみ回数が多い順) --}}
                    <h3 class="text-xl font-bold text-gray-800 mb-4">本日のランキング<br><span class="text-sm font-normal text-gray-600">くしゃみ回数が多い順</span></h3>
                    @php
                    // ダミーデータ (実際はコントローラーから渡す)
                    $nationalSneezeRankings = [
                    ['rank' => 1, 'prefecture' => '北海道', 'sneeze_count' => 6, 'avg_level' => 4],
                    // 他のランキングデータ...
                    ];
                    @endphp
                    @if(!empty($nationalSneezeRankings))
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">順位</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">都道府県</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">回数</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">平均辛さレベル</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($nationalSneezeRankings as $ranking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @if($ranking['rank'] == 1) <span class="text-yellow-500 text-lg font-bold">🥇 1位</span>
                                        @elseif($ranking['rank'] == 2) <span class="text-gray-400 text-lg font-bold">🥈 2位</span>
                                        @elseif($ranking['rank'] == 3) <span class="text-orange-500 text-lg font-bold">🥉 3位</span>
                                        @else {{ $ranking['rank'] }}位
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800">{{ $ranking['prefecture'] }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-800 text-right">{{ $ranking['sneeze_count'] }}回</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-800 text-right">{{ $ranking['avg_level'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <p>本日の全国くしゃみランキングデータはまだ集計されていません。</p>
                    </div>
                    @endif
                    @elseif (($currentTab ?? '') === 'personal')
                    {{-- 個人ランキング表示 (画像にはないので今回は簡易的に作成) --}}
                    <h3 class="text-xl font-bold text-gray-800 mb-4">本日のランキング<br><span class="text-sm font-normal text-gray-600">くしゃみ回数が多い順</span></h3>
                    @php
                    // ダミーデータ (実際はコントローラーから渡す)
                    $personalRankings = [
                    'rank' => 1,
                    'sneeze_count' => 6,
                    'avg_level' => 4,
                    ];
                    @endphp
                    <div class="flex items-center justify-start p-4 bg-purple-500 text-white rounded-lg shadow-md">
                        <span class="text-5xl mr-4">👑</span>
                        <div>
                            <p class="text-lg font-bold">{{ $personalRankings['sneeze_count'] }}回</p>
                            <p class="text-sm">平均辛さレベル: {{ $personalRankings['avg_level'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ランキングについての注釈 --}}
            <div class="bg-gray-100 text-gray-700 p-6 rounded-2xl shadow-sm">
                <h4 class="font-bold text-lg mb-2">ランキングについて</h4>
                <p class="text-sm leading-relaxed">
                    このランキングは、本日のくしゃみデータを集計したものです。全国のユーザーがどの程度くしゃみに悩んでいるかを測ることで、あなたの状況を相対的に理解できます。
                    ランキングに参加するには、「くしゃみを記録」してください。あなたのデータが集計され表示されます。
                </p>
            </div>

        </div>
    </div>
</x-app-layout>