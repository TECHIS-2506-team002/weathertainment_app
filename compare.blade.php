<x-app-layout>

{{-- デバッグ情報 --}}
<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid red;">
    <h3>デバッグ情報</h3>
    <pre>
ユーザーID: {{ $user->id ?? '未ログイン' }}
アレルギー敏感度: {{ $user->allergy_sensitivity ?? 'null' }}
気温敏感度: {{ $user->temperature_sensitivity ?? 'null' }}
天気敏感度: {{ $user->weather_sensitivity ?? 'null' }}

鼻タイプ: {{ $userNoseType }} (設定済み: {{ $hasNoseType ? 'はい' : 'いいえ' }})
くしゃみ確率算出方法: {{ $sneezeRateCalculationMethod }}
    </pre>
</div>

    <style>
        /* ヘッダーの背景グラデーション */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap');

        /* 本日のくしゃみ確率ゲージのスタイルを棒グラフに変更 */
        .sneeze-bar-chart-container {
            width: 1000px; /* 必要に応じて調整 */
            max-width: 100%; /* レスポンシブ対応 */
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
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .nav-button-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .nav-button.pink { background: linear-gradient(to right, #F06292, #E91E63); }
        .nav-button.blue { background: linear-gradient(to right, #42A5F5, #2196F3); }
        .nav-button.orange { background: linear-gradient(to right, #FFB74D, #FF9800); }
        .nav-button.purple { background: linear-gradient(to right, #AB47BC, #9C27B0); }

        /* ヘルプアイコンとツールチップのスタイル */
        .help-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .help-icon {
            color: rgba(255, 255, 255, 0.7);
            margin-left: 5px;
            font-size: 0.9em;
        }
        .help-tooltip {
            visibility: hidden;
            width: 250px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 10px;
            position: absolute;
            z-index: 10;
            bottom: 125%; /* アイコンの上に表示 */
            /* leftとmargin-leftはJavaScriptで制御するため削除 */
            /* left: 50%; */
            /* margin-left: -125px; */
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8em;
            line-height: 1.4;
            /* 初期状態では中央寄せにしておく（JSで調整が入らなかった場合） */
            left: 50%;
            transform: translateX(-50%);
        }
        .help-tooltip::after {
            content: "";
            position: absolute;
            top: 100%; /* ツールチップの下に三角形 */
            /* 矢印の位置はJSで制御、デフォルトは中央 */
            left: var(--arrow-left, 50%);
            transform: translateX(-50%);
            margin-left: 0; /* JSでleftを制御する場合はこれも0に */
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
        }
        .help-container:hover .help-tooltip {
            /* hover時のスタイルはJSで制御するため、ここから削除 */
            /* visibility: visible;
            opacity: 1; */
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
                    @if (!$hasNoseType && $user) {{-- ユーザーがログインしている場合にのみ表示 --}}
                        <br><span class="text-yellow-200 text-xs">※{{ $sneezeRateNote }}</span>
                    @endif
                </p>
                <div class="text-right text-xs opacity-70 flex items-center justify-end">
                    信頼度: {{ $sneezeReliability }}%
                    <div class="help-container">
                        <span class="help-icon">ⓘ</span>
                        <div class="help-tooltip">
                            この信頼度は、くしゃみ確率の算出に使用されたデータの網羅性と精度を示します。<br><br>
                            ・天気情報のみの場合: 信頼度は最大80%です。<br>
                            ・体質情報も設定済みの場合: 最大90%まで上昇します。
                        </div>
                    </div>
                </div>
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
                @if (!$hasNoseType)
                    <a href="{{ route('profile.edit') }}" class="text-xs mt-4 inline-block text-white/80 hover:text-white underline">
                        体質情報を設定する &gt;
                    </a>
                @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const helpContainers = document.querySelectorAll('.help-container');

    helpContainers.forEach(container => {
        const tooltip = container.querySelector('.help-tooltip');
        const helpIcon = container.querySelector('.help-icon');

        let hideTimeout;

        container.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout); // マウスが再進入した場合に非表示タイマーをクリア

            tooltip.style.visibility = 'hidden'; // 計算のために一時的に非表示
            tooltip.style.opacity = '0';
            tooltip.style.left = '50%'; // 初期位置を中央に設定
            tooltip.style.right = 'auto'; // 右側の固定を解除
            tooltip.style.transform = 'translateX(-50%)'; // 初期位置を中央に設定
            tooltip.style.setProperty('--arrow-left', '50%'); // 矢印も中央にリセット

            // レイアウト計算が完了するまで待つ
            requestAnimationFrame(() => {
                const rect = tooltip.getBoundingClientRect();
                const iconRect = helpIcon.getBoundingClientRect();
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;

                // 左右の余白（ツールチップが画面端に密着しすぎないように）
                const padding = 10;

                // デフォルトの矢印位置 (ツールチップ中央からの相対位置)
                let arrowLeft = '50%';

                // 左側で見切れる場合
                if (rect.left < padding) {
                    tooltip.style.left = padding + 'px';
                    tooltip.style.transform = 'translateX(0)'; // 左端に寄せるのでtransformは不要
                    tooltip.style.right = 'auto'; // 右側の固定を解除

                    // 矢印の位置をアイコンの中央に合わせる
                    const offsetFromTooltipLeft = iconRect.left - padding + (iconRect.width / 2);
                    arrowLeft = `${offsetFromTooltipLeft}px`;
                }
                // 右側で見切れる場合
                else if (rect.right > viewportWidth - padding) {
                    tooltip.style.left = 'auto'; // leftをautoにして
                    tooltip.style.right = padding + 'px'; // rightから調整
                    tooltip.style.transform = 'translateX(0)'; // 右端に寄せるのでtransformは不要

                    // 矢印の位置をアイコンの中央に合わせる
                    const offsetFromTooltipRight = (viewportWidth - padding) - iconRect.right + (iconRect.width / 2);
                    arrowLeft = `calc(100% - ${offsetFromTooltipRight}px)`;
                }

                tooltip.style.setProperty('--arrow-left', arrowLeft); // 矢印の位置を更新
                tooltip.style.visibility = 'visible';
                tooltip.style.opacity = '1';
            });
        });

        // マウスアウト時にツールチップを非表示にする
        container.addEventListener('mouseleave', function() {
            // 少し遅延させて、すぐに再進入した場合にチラつきを防ぐ
            hideTimeout = setTimeout(() => {
                tooltip.style.visibility = 'hidden';
                tooltip.style.opacity = '0';
            }, 100); // 100msの遅延
        });
    });
});
</script>
</x-app-layout>