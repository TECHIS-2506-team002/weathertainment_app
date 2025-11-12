<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                くしゃみを記録
            </h2>
        </div>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F8F8] min-h-screen">
        <div class="max-w-xl mx-auto">

            {{-- くしゃみ記録ヘッダーカード --}}
            <div class="dashboard-card bg-gradient-to-r from-purple-600 to-pink-500 text-white p-6 mb-6">
                <h3 class="text-xl font-bold mb-1">くしゃみを記録</h3>
                <p class="text-sm opacity-90">辛さレベルと回数を選択してください</p>
            </div>

            {{-- 辛さレベル選択 --}}
            <div class="dashboard-card p-6 mb-6">
                <h4 class="text-lg font-bold text-gray-800 mb-4">辛さレベル: <span id="sneeze-level-display">3</span></h4>
                <div class="grid grid-cols-5 gap-3 mb-4">
                    <button type="button" class="sneeze-level-button py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" data-level="1">軽</button>
                    <button type="button" class="sneeze-level-button py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" data-level="2">弱</button>
                    <button type="button" class="sneeze-level-button py-3 rounded-xl bg-pink-500 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-opacity-50" data-level="3">中</button>
                    <button type="button" class="sneeze-level-button py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" data-level="4">強</button>
                    <button type="button" class="sneeze-level-button py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" data-level="5">激</button>
                </div>
                <p class="text-sm text-gray-500">1=軽い、5=激しい</p>
            </div>

            {{-- くしゃみ回数 --}}
            <div class="dashboard-card p-6 mb-6">
                <h4 class="text-lg font-bold text-gray-800 mb-4">くしゃみ回数</h4>
                <div class="flex items-center justify-center space-x-4">
                    <button type="button" id="decrement-sneeze-count" class="w-12 h-12 rounded-full bg-gray-200 text-gray-700 text-2xl font-bold flex items-center justify-center hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">-</button>
                    <span id="sneeze-count-display" class="text-5xl font-extrabold text-gray-800">1</span>
                    <button type="button" id="increment-sneeze-count" class="w-12 h-12 rounded-full bg-gray-200 text-gray-700 text-2xl font-bold flex items-center justify-center hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">+</button>
                </div>
            </div>

            {{-- メモ (オプション) --}}
            <div class="dashboard-card p-6 mb-8">
                <h4 class="text-lg font-bold text-gray-800 mb-4">メモ (オプション)</h4>
                <textarea class="w-full p-3 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-colors" rows="4" placeholder="例: 外出中、花粉が多い気がする…"></textarea>
            </div>

            {{-- 記録するボタン --}}
            <button type="submit" class="w-full bg-pink-500 text-white py-4 rounded-xl text-xl font-bold hover:bg-pink-600 transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-opacity-50">
                記録する
            </button>

            {{-- ヒント --}}
            <div class="bg-blue-50 text-blue-800 p-6 rounded-2xl mt-8 shadow-sm">
                <h4 class="font-bold mb-2">ヒント</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>辛さレベルは、くしゃみの強さで判断してください</li>
                    <li>複数回連続でくしゃみが出た場合は、回数を増やしてください</li>
                    <li>メモに環境や状況を記録すると、原因分析がより正確になります</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // くしゃみレベル選択
            const levelButtons = document.querySelectorAll('.sneeze-level-button');
            const sneezeLevelDisplay = document.getElementById('sneeze-level-display');
            let currentSneezeLevel = 3; // デフォルト値

            levelButtons.forEach(button => {
                if (parseInt(button.dataset.level) === currentSneezeLevel) {
                    button.classList.remove('bg-gray-100', 'text-gray-700');
                    button.classList.add('bg-pink-500', 'text-white');
                }

                button.addEventListener('click', function() {
                    levelButtons.forEach(btn => {
                        btn.classList.remove('bg-pink-500', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-700');
                    });
                    this.classList.remove('bg-gray-100', 'text-gray-700');
                    this.classList.add('bg-pink-500', 'text-white');
                    currentSneezeLevel = parseInt(this.dataset.level);
                    sneezeLevelDisplay.textContent = currentSneezeLevel;
                });
            });

            // くしゃみ回数カウンター
            const decrementButton = document.getElementById('decrement-sneeze-count');
            const incrementButton = document.getElementById('increment-sneeze-count');
            const sneezeCountDisplay = document.getElementById('sneeze-count-display');
            let sneezeCount = 1; // デフォルト値

            decrementButton.addEventListener('click', function() {
                if (sneezeCount > 1) {
                    sneezeCount--;
                    sneezeCountDisplay.textContent = sneezeCount;
                }
            });

            incrementButton.addEventListener('click', function() {
                sneezeCount++;
                sneezeCountDisplay.textContent = sneezeCount;
            });
        });
    </script>
    @endpush
</x-app-layout>