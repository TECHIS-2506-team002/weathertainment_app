<x-app-layout>
    <style>
        /* ヘッダーの背景グラデーション */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap');

        /* 本日のくしゃみ確率ゲージのスタイルを棒グラフに変更 */
        .sneeze-bar-chart-container {
            width: 1000px;
            /* 必要に応じて調整 */
            max-width: 100%;
            /* レスポンシブ対応 */
            height: 20px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            display: flex;
            align-items: center;
            overflow: hidden;
            margin-left: auto;
        }

        .sneeze-bar-chart-fill {
            height: 100%;
            background-color: white;
            border-radius: 5px;
            transition: width 0.5s ease-out;
        }

        /* 共通のカードスタイル */
        .dashboard-card {
            background-color: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        /* ナビゲーションボタンのスタイル */
        .nav-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem 0.5rem;
            border-radius: 1.5rem;
            text-align: center;
            font-weight: 600;
            color: white;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-button-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .nav-button.pink {
            background: linear-gradient(to right, #F06292, #E91E63);
        }

        .nav-button.blue {
            background: linear-gradient(to right, #42A5F5, #2196F3);
        }

        .nav-button.orange {
            background: linear-gradient(to right, #FFB74D, #FF9800);
        }

        .nav-button.purple {
            background: linear-gradient(to right, #AB47BC, #9C27B0);
        }
    </style>

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F8F8] min-h-screen">

        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- 1. 本日のくしゃみ確率 --}}
            <div class="dashboard-card bg-gradient-to-r from-purple-600 to-pink-500 text-white p-8 relative overflow-hidden col-span-full">
                <h3 class="text-2xl font-bold mb-4">本日のくしゃみ確率</h3>
                <div class="flex items-center justify-between mb-4">
                    <p class="text-6xl font-extrabold">{{ $sneezeRate }}%</p>
                    <div class="sneeze-bar-chart-container">
                        <div class="sneeze-bar-chart-fill" style="width: {{ is_numeric($sneezeRate) ? $sneezeRate : 0 }}%;"></div>
                    </div>
                </div>
                <p class="text-sm opacity-90 mb-4">
                    @if ($sneezeRate === 'N/A')
                    現在、くしゃみ確率を算出できません。天気情報が取得できない可能性があります。
                    @elseif (($sneezeRate ?? 0) >= 70)
                    今日はくしゃみに要注意！外出時はマスクを忘れずに。
                    @elseif (($sneezeRate ?? 0) >= 40)
                    油断は禁物。時々鼻がムズムズするかも。
                    @else
                    今日は比較的快適に過ごせそうです。
                    @endif
                    @if (!$hasNoseType)
                    <br><span class="text-yellow-200">※{{ $sneezeRateNote }}</span>
                    @endif
                </p>
                <div class="text-right text-xs opacity-70">信頼度: {{ $sneezeReliability }}%</div>
            </div>

            {{-- 2. 地域選択と天気情報カード --}}
            <div class="dashboard-card col-span-full md:col-span-2 lg:col-span-2">
                {{-- 地域選択 --}}
                <div class="mb-6">
                    <x-region-selector :currentPrefecture="$selectedCity" />
                </div>

                @if (isset($weatherData) && $weatherData)
                {{-- 天気情報 --}}
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $weatherData['name'] }} の今日の予報</h2>
                    <p class="text-gray-600 text-lg mb-2 capitalize">{{ $weatherData['weather'][0]['description'] }}</p>
                    <p class="text-red-500 text-5xl font-bold">{{ number_format($weatherData['main']['temp'], 1) }} °C</p>
                    <p class="text-blue-500 text-2xl font-semibold mt-1">{{ $weatherData['main']['humidity'] }}%</p>
                </div>
                @else
                {{-- 天気情報取得失敗時の表示 --}}
                <div class="text-center text-red-500 py-10">
                    <p class="font-bold text-lg">天気情報の取得に失敗しました。</p>
                    <p class="text-sm mt-2">APIキーの設定を確認するか、地域名を変更してください。</p>
                </div>
                @endif
            </div>

            {{-- 3. あなたの鼻タイプカード --}}
            <div class="dashboard-card bg-gradient-to-r from-yellow-400 to-orange-500 text-white p-8 col-span-full md:col-span-1">
                <h3 class="text-2xl font-bold mb-4">あなたの鼻タイプ</h3>
                <div class="flex items-center mb-4">
                    <span class="text-6xl mr-4">{{ $userNoseTypeIcon }}</span>
                    <p class="text-3xl font-bold">
                        {{ $userNoseType }}
                    </p>
                </div>
                <p class="text-sm opacity-90">
                    {{ $userNoseTypeDescription }}
                </p>
                <a href="{{ route('profile.edit') }}" class="text-xs mt-4 inline-block text-white/80 hover:text-white underline">
                    体質情報を設定する &gt;
                </a>
            </div>

            {{-- 4. 下部ナビゲーションカード --}}
            <div class="col-span-full grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8">
                <a href="{{ route('sneeze.create') }}" class="nav-button pink">
                    <span class="nav-button-icon">📝</span>
                    くしゃみを記録
                </a>
                <a href="{{ route('sneeze.record') }}" class="nav-button blue">
                    <span class="nav-button-icon">📊</span>
                    統計
                </a>
                <a href="{{ route('ranking') }}" class="nav-button orange">
                    <span class="nav-button-icon">🏆</span>
                    ランキング
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-button purple">
                    <span class="nav-button-icon">⚙️</span>
                    設定
                </a>
            </div>

            {{-- Twitterシェアボタン --}}
            <div class="col-span-full mt-8">
                <a href="{{ $twitterShareUrl }}" target="_blank" class="w-full bg-black text-white py-3 px-4 rounded-full flex items-center justify-center space-x-2 hover:bg-gray-800 transition-colors font-bold text-base">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 1200 1227">
                        <path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.924L144.011 79.6904H306.615L611.412 515.685L658.88 583.589L1058.01 1154.97H895.408L569.165 687.854V687.828Z" />
                    </svg>
                    <span>結果をXでシェアする</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>