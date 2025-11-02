<x-app-layout>
    <div class="mx-auto py-12 px-4 sm:px-6 lg:px-8">

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
        <div class="w-full mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">データ更新日</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['updateDate'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">全国平均{{ ['sneeze' => 'くしゃみ', 'fringe_collapse' => '前髪崩壊', 'laundry_mold' => '洗濯カビ'][$selectedType] ?? '' }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['averageScore'] }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <p class="text-sm font-medium text-gray-500">対象都道府県</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['prefectureCount'] }} <span class="text-base">都道府県</span></p>
            </div>
        </div>

        {{-- ランキング表示セクション --}}
        <div class="w-full mx-auto bg-white rounded-2xl shadow-2xl p-6 sm:p-8 mb-8">
            <div class="p-0">
                <div class="flex border-b border-gray-200 mb-6">
                    <a href="{{ route('ranking', ['type' => 'sneeze']) }}" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'sneeze' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">くしゃみ確率</a>
                    <a href="{{ route('ranking', ['type' => 'fringe_collapse']) }}" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'fringe_collapse' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">前髪崩壊率</a>
                    <a href="{{ route('ranking', ['type' => 'laundry_mold']) }}" class="flex-1 text-center tab-button px-4 sm:px-6 py-3 text-sm font-medium {{ $selectedType === 'laundry_mold' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">洗濯物カビリスク</a>
                </div>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ ['sneeze' => 'くしゃみ確率ランキング', 'fringe_collapse' => '前髪崩壊率ランキング', 'laundry_mold' => '洗濯物カビリスクランキング'][$selectedType] ?? '' }}
                    </h3>
                    <form method="POST" action="{{ route('ranking.update') }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-1 transition-transform transform hover:scale-105"><span>更新</span></button>
                    </form>
                </div>
                @if($rankings[$selectedType]->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        {{-- (テーブルの中身は変更なし) --}}
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
                    <p class="text-gray-500">本日のランキングデータはまだ集計されていません。</p>
                    <p class="text-sm mt-1 text-gray-400">（`php artisan ranking:update` を実行してください）</p>
                </div>
                @endif
            </div>
        </div>

        {{-- 週間推移セクション --}}
        <div class="w-full mx-auto bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            <div class="p-0">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">週間推移（{{ $chartData['prefecture'] }}）</h3>

                {{-- 週間推移フォーム --}}
                <form action="{{ route('ranking') }}" method="GET" class="flex items-center gap-2 mb-6">

                    {{-- 指数選択（name="type"） --}}
                    <select name="type" class="block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="sneeze" {{ $selectedType === 'sneeze' ? 'selected' : '' }}>くしゃみ確率</option>
                        <option value="fringe_collapse" {{ $selectedType === 'fringe_collapse' ? 'selected' : '' }}>前髪崩壊率</option>
                        <option value="laundry_mold" {{ $selectedType === 'laundry_mold' ? 'selected' : '' }}>洗濯物カビリスク</option>
                    </select>

                    {{-- 都道府県選択（name="chart_prefecture"） --}}
                    <select name="chart_prefecture" class="block w-full border-gray-300 rounded-md shadow-sm">
                        @php
                        $prefectures = ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'];
                        $currentPrefecture = $chartData['prefecture'];
                        @endphp
                        @foreach ($prefectures as $prefecture)
                        <option value="{{ $prefecture }}" {{ $currentPrefecture === $prefecture ? 'selected' : '' }}>
                            {{ $prefecture }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600 shrink-0">
                        表示
                    </button>
                </form>

                {{-- グラフ描画エリア --}}
                @if(!empty($chartData['scores']) && collect($chartData['scores'])->some(fn($score) => $score > 0))
                <div><canvas id="weeklyChart"></canvas></div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-500">表示できる過去のデータが十分にありません。</p>
                    <p class="text-sm mt-1 text-gray-400">（ランキングを数日間更新すると表示されます）</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Chart.jsの読み込みとグラフ描画スクリプト --}}
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chartData);

                if (chartData.scores && chartData.scores.some(score => score > 0)) {
                    const ctx = document.getElementById('weeklyChart').getContext('2d');
                    const weeklyChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: chartData.label,
                                data: chartData.scores,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                tension: 0.1,
                                fill: true,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointHoverRadius: 7,
                                pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
        @endpush
</x-app-layout>