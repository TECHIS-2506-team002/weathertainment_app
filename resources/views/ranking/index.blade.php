<x-app-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

        {{-- メインタイトル --}}
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-2">
                <svg class="w-16 h-16 text-white mr-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <h1 class="text-4xl font-bold text-white">全国生活指数ランキング</h1>
            </div>
            <p class="text-lg text-white/80">今日の全国47都道府県の生活指数ランキングをチェック！</p>
        </div>

        {{-- 統計情報カード --}}
        <div class="w-full  mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">データ更新日</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['updateDate'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">平均{{ $selectedType === 'sneeze' ? 'くしゃみ' : ($selectedType === 'fringe_collapse' ? '前髪崩壊' : '洗濯カビ') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['averageScore'] }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">平均風速</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">-- m/s</p>
            </div>
        </div>

        {{-- ランキング表示セクション --}}
        <div class="w-full  mx-auto bg-white rounded-2xl shadow-2xl p-6 sm:p-8 mb-8">
            <div class="p-0">
                <!-- タブ切り替え -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button onclick="switchTab('sneeze')" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'sneeze' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">くしゃみ確率</button>
                    <button onclick="switchTab('fringe_collapse')" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'fringe_collapse' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">前髪崩壊率</button>
                    <button onclick="switchTab('laundry_mold')" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'laundry_mold' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">洗濯物カビリスク</button>
                </div>

                <!-- ランキングタイトルと更新ボタン -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ $selectedType === 'sneeze' ? 'くしゃみ確率ランキング' : ($selectedType === 'fringe_collapse' ? '前髪崩壊率ランキング' : '洗濯物カビリスクランキング') }}
                    </h3>
                    <form method="POST" action="{{ route('ranking.update') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-1"><span>更新</span></button>
                    </form>
                </div>

                <!-- ランキングテーブル -->
                @if($rankings[$selectedType]->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">順位</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">都道府県</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">スコア</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rankings[$selectedType] as $ranking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    @if($ranking->rank == 1) <span class="text-yellow-500 text-lg font-bold">🥇 1位</span>
                                    @elseif($ranking->rank == 2) <span class="text-gray-400 text-lg font-bold">🥈 2位</span>
                                    @elseif($ranking->rank == 3) <span class="text-orange-500 text-lg font-bold">🥉 3位</span>
                                    @else {{ $ranking->rank }}位
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $ranking->prefecture }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 text-right">{{ $ranking->score }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-500">データがありません (`php artisan ranking:update` を実行してください)</p>
                </div>
                @endif
            </div>
        </div>

        {{-- 週間推移セクション --}}
        <div class="w-full  mx-auto bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            <div class="p-0">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">週間推移</h3>
                <div class="flex items-center mb-6">
                    <select id="trendType" class="mr-4 border border-gray-300 bg-white text-gray-900 rounded-md px-3 py-2">
                        <option value="sneeze" {{ $selectedType === 'sneeze' ? 'selected' : '' }}>くしゃみ確率</option>
                        <option value="fringe_collapse" {{ $selectedType === 'fringe_collapse' ? 'selected' : '' }}>前髪崩壊率</option>
                        <option value="laundry_mold" {{ $selectedType === 'laundry_mold' ? 'selected' : '' }}>洗濯物カビリスク</option>
                    </select>
                    <button onclick="showTrend()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg"><span>推移を表示</span></button>
                </div>
                <div id="chartContainer" class="hidden"><canvas id="trendChart" width="400" height="200"></canvas></div>
                <div id="chartPlaceholder" class="text-center py-12">
                    <p class="text-gray-500">推移を表示するには上のボタンをクリックしてください</p>
                </div>
            </div>
        </div>
    </div>

    <div id="chart-data" data-weekly-trends="{{ json_encode($weeklyData) }}"></div>

    <script>
        // タブ切り替え機能
        function switchTab(type) {
            window.location.href = `{{ route('ranking') }}?type=${type}`;
        }

        // 週間推移グラフ表示
        function showTrend() {
            const type = document.getElementById('trendType').value;
            const chartContainer = document.getElementById('chartContainer');
            const chartPlaceholder = document.getElementById('chartPlaceholder');

            const weeklyDataJson = document.getElementById('chart-data').getAttribute('data-weekly-trends');
            const weeklyData = JSON.parse(weeklyDataJson);

            // プレースホルダーを非表示、チャートを表示
            chartPlaceholder.classList.add('hidden');
            chartContainer.classList.remove('hidden');

            // 既存のチャートを破棄
            if (window.trendChart) {
                window.trendChart.destroy();
            }

            const ctx = document.getElementById('trendChart').getContext('2d');
            window.trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: weeklyData.map(item => item.date),
                    datasets: [{
                        label: '平均スコア',
                        data: weeklyData.map(item => item.averageScore),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: '最高スコア',
                        data: weeklyData.map(item => item.topScore),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>