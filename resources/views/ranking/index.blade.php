<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ランキング
        </h2>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F8F8] min-h-screen">
        <div class="max-w-4xl mx-auto space-y-8">

            {{-- ★★★ 文言修正 ★★★ --}}
            {{-- 全国くしゃみ最多エリア カード --}}
            @if($worstSneezePrefecture)
            <div class="dashboard-card p-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-white relative overflow-hidden">
                <div class="flex items-center mb-4">
                    <span class="text-4xl mr-3">🏆</span>
                    {{-- 「ワースト1位」→「最多エリア」に変更 --}}
                    <h3 class="text-2xl font-bold">くしゃみ最多エリア</h3>
                </div>
                <p class="text-sm opacity-90 mb-6">本日最もくしゃみが多かった地域</p>

                <div class="flex items-center justify-around text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-5xl">👑</span>
                        <p class="text-2xl font-extrabold mt-2">{{ $worstSneezePrefecture->rank }}</p>
                        <p class="text-sm opacity-90">位 {{ $worstSneezePrefecture->prefecture }}</p>
                    </div>
                    <div class="border-l border-white/30 h-24 mx-4"></div>
                    <div>
                        <p class="text-4xl font-extrabold">{{ $worstSneezePrefecture->total_count }}</p>
                        <p class="text-sm opacity-90">くしゃみ回数</p>
                    </div>
                    <div class="border-l border-white/30 h-24 mx-4"></div>
                    <div>
                        <p class="text-4xl font-extrabold">{{ number_format($worstSneezePrefecture->average_level, 1) }}</p>
                        <p class="text-sm opacity-90">平均辛さレベル</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- ランキングタブ切り替え --}}
            <div class="dashboard-card p-0">
                <div class="flex border-b border-gray-200">
                    <a href="{{ route('ranking', ['tab' => 'national']) }}" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-lg font-medium {{ $currentTab === 'national' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">全国ランキング</a>
                    <a href="{{ route('ranking', ['tab' => 'personal']) }}" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-lg font-medium {{ $currentTab === 'personal' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">個人ランキング</a>
                </div>

                <div class="p-6">
                    @if ($currentTab === 'national')
                    {{-- 全国ランキング表示 --}}
                    <h3 class="text-xl font-bold text-gray-800 mb-4">本日のランキング<br><span class="text-sm font-normal text-gray-600">くしゃみ回数が多い順</span></h3>

                    @if($nationalRankings->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">順位</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">都道府県</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">回数</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">平均辛さレベル</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($nationalRankings as $ranking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @if($ranking->rank == 1) <span class="text-yellow-500 text-lg font-bold">🥇 1位</span>
                                        @elseif($ranking->rank == 2) <span class="text-gray-400 text-lg font-bold">🥈 2位</span>
                                        @elseif($ranking->rank == 3) <span class="text-orange-500 text-lg font-bold">🥉 3位</span>
                                        @else {{ $ranking->rank }}位
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800">{{ $ranking->prefecture }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-800 text-right">{{ $ranking->total_count }}回</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-800 text-right">{{ number_format($ranking->average_level, 1) }}</td>
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
                    @elseif ($currentTab === 'personal')
                    {{-- 個人ランキング表示 (小林さん担当) --}}
                    <h3 class="text-xl font-bold text-gray-800 mb-4">あなたの本日の記録</h3>
                    @if($personalRankings && $personalRankings['sneeze_count'] > 0)
                    <div class="flex items-center justify-start p-4 bg-purple-500 text-white rounded-lg shadow-md">
                        <span class="text-5xl mr-4">
                            @if($personalRankings['rank']) 👑 @else 🤧 @endif
                        </span>
                        <div>
                            @if($personalRankings['rank'])
                            <p class="text-lg font-bold">全国 {{ $personalRankings['rank'] }} 位 ({{ $personalRankings['prefecture'] }})</p>
                            @endif
                            <p class="text-lg font-bold">{{ $personalRankings['sneeze_count'] }}回</p>
                            <p class="text-sm">平均辛さレベル: {{ $personalRankings['avg_level'] }}</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <p>本日のあなたのくしゃみ記録はまだありません。</p>
                        <a href="{{ route('sneeze.create') }}" class="text-blue-500 hover:underline">記録しに行きましょう！</a>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- ランキングについての注釈 --}}
            <div class="bg-gray-100 text-gray-700 p-6 rounded-2xl shadow-sm">
                <h4 class="font-bold text-lg mb-2">ランキングについて</h4>
                <p class="text-sm leading-relaxed">
                    この全国ランキングは、毎日深夜に、その日に記録された全てのくしゃみログを都道府県別に集計して作成されます。
                    あなたが「くしゃみを記録」することで、あなたの都道府県のデータに貢献できます！
                </p>
            </div>
        </div>
    </div>
</x-app-layout>