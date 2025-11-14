@props(['currentPrefecture' => null])

<form action="{{ url()->current() }}" method="GET" class="w-full">
    <div class="relative">
        <select name="prefecture" id="prefecture-select" class="w-full bg-white text-gray-900 py-3 px-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer" onchange="this.form.submit()">
            @php
            $prefectures = [
                '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
                '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
                '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
                '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
                '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
                '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
                '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
            ];
            @endphp
            
            {{-- 未選択の場合のプレースホルダー --}}
            @if(!$currentPrefecture)
                <option value="" selected>所在地を選択してください</option>
            @endif
            
            @foreach ($prefectures as $prefecture)
                <option value="{{ $prefecture }}" @if($currentPrefecture === $prefecture) selected @endif>
                    {{ $prefecture }}
                </option>
            @endforeach
        </select>
        {{-- カスタム矢印アイコン --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</form>           

{{-- -Deleted 2025.11.13 Tonoyama --}}
<!-- @props(['currentPrefecture' => '東京都'])

<form action="{{ url()->current() }}" method="GET" class="w-full">
    <div class="relative">
        <select name="prefecture" id="prefecture-select" class="w-full bg-white text-gray-900 py-3 px-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer" onchange="this.form.submit()">
            @php
            $prefectures = [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
            ];
            @endphp
            @foreach ($prefectures as $prefecture)
            <option value="{{ $prefecture }}" @if($prefecture===$currentPrefecture) selected @endif>
                {{ $prefecture }}
            </option>
            @endforeach
        </select>
        {{-- カスタム矢印アイコン --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</form> -->