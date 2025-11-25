<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('ユーザー情報') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("あなたの基本情報を教えてください") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('名前')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- 都道府県 -->
        <div>
            <x-input-label for="prefecture" :value="__('お住まいの都道府県')" />
            <select id="prefecture" name="prefecture" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">選択してください</option>
                <option value="北海道" {{ old('prefecture', $user->prefecture) == '北海道' ? 'selected' : '' }}>北海道</option>
                <option value="青森県" {{ old('prefecture', $user->prefecture) == '青森県' ? 'selected' : '' }}>青森県</option>
                <option value="岩手県" {{ old('prefecture', $user->prefecture) == '岩手県' ? 'selected' : '' }}>岩手県</option>
                <option value="宮城県" {{ old('prefecture', $user->prefecture) == '宮城県' ? 'selected' : '' }}>宮城県</option>
                <option value="秋田県" {{ old('prefecture', $user->prefecture) == '秋田県' ? 'selected' : '' }}>秋田県</option>
                <option value="山形県" {{ old('prefecture', $user->prefecture) == '山形県' ? 'selected' : '' }}>山形県</option>
                <option value="福島県" {{ old('prefecture', $user->prefecture) == '福島県' ? 'selected' : '' }}>福島県</option>
                <option value="茨城県" {{ old('prefecture', $user->prefecture) == '茨城県' ? 'selected' : '' }}>茨城県</option>
                <option value="栃木県" {{ old('prefecture', $user->prefecture) == '栃木県' ? 'selected' : '' }}>栃木県</option>
                <option value="群馬県" {{ old('prefecture', $user->prefecture) == '群馬県' ? 'selected' : '' }}>群馬県</option>
                <option value="埼玉県" {{ old('prefecture', $user->prefecture) == '埼玉県' ? 'selected' : '' }}>埼玉県</option>
                <option value="千葉県" {{ old('prefecture', $user->prefecture) == '千葉県' ? 'selected' : '' }}>千葉県</option>
                <option value="東京都" {{ old('prefecture', $user->prefecture) == '東京都' ? 'selected' : '' }}>東京都</option>
                <option value="神奈川県" {{ old('prefecture', $user->prefecture) == '神奈川県' ? 'selected' : '' }}>神奈川県</option>
                <option value="新潟県" {{ old('prefecture', $user->prefecture) == '新潟県' ? 'selected' : '' }}>新潟県</option>
                <option value="富山県" {{ old('prefecture', $user->prefecture) == '富山県' ? 'selected' : '' }}>富山県</option>
                <option value="石川県" {{ old('prefecture', $user->prefecture) == '石川県' ? 'selected' : '' }}>石川県</option>
                <option value="福井県" {{ old('prefecture', $user->prefecture) == '福井県' ? 'selected' : '' }}>福井県</option>
                <option value="山梨県" {{ old('prefecture', $user->prefecture) == '山梨県' ? 'selected' : '' }}>山梨県</option>
                <option value="長野県" {{ old('prefecture', $user->prefecture) == '長野県' ? 'selected' : '' }}>長野県</option>
                <option value="岐阜県" {{ old('prefecture', $user->prefecture) == '岐阜県' ? 'selected' : '' }}>岐阜県</option>
                <option value="静岡県" {{ old('prefecture', $user->prefecture) == '静岡県' ? 'selected' : '' }}>静岡県</option>
                <option value="愛知県" {{ old('prefecture', $user->prefecture) == '愛知県' ? 'selected' : '' }}>愛知県</option>
                <option value="三重県" {{ old('prefecture', $user->prefecture) == '三重県' ? 'selected' : '' }}>三重県</option>
                <option value="滋賀県" {{ old('prefecture', $user->prefecture) == '滋賀県' ? 'selected' : '' }}>滋賀県</option>
                <option value="京都府" {{ old('prefecture', $user->prefecture) == '京都府' ? 'selected' : '' }}>京都府</option>
                <option value="大阪府" {{ old('prefecture', $user->prefecture) == '大阪府' ? 'selected' : '' }}>大阪府</option>
                <option value="兵庫県" {{ old('prefecture', $user->prefecture) == '兵庫県' ? 'selected' : '' }}>兵庫県</option>
                <option value="奈良県" {{ old('prefecture', $user->prefecture) == '奈良県' ? 'selected' : '' }}>奈良県</option>
                <option value="和歌山県" {{ old('prefecture', $user->prefecture) == '和歌山県' ? 'selected' : '' }}>和歌山県</option>
                <option value="鳥取県" {{ old('prefecture', $user->prefecture) == '鳥取県' ? 'selected' : '' }}>鳥取県</option>
                <option value="島根県" {{ old('prefecture', $user->prefecture) == '島根県' ? 'selected' : '' }}>島根県</option>
                <option value="岡山県" {{ old('prefecture', $user->prefecture) == '岡山県' ? 'selected' : '' }}>岡山県</option>
                <option value="広島県" {{ old('prefecture', $user->prefecture) == '広島県' ? 'selected' : '' }}>広島県</option>
                <option value="山口県" {{ old('prefecture', $user->prefecture) == '山口県' ? 'selected' : '' }}>山口県</option>
                <option value="徳島県" {{ old('prefecture', $user->prefecture) == '徳島県' ? 'selected' : '' }}>徳島県</option>
                <option value="香川県" {{ old('prefecture', $user->prefecture) == '香川県' ? 'selected' : '' }}>香川県</option>
                <option value="愛媛県" {{ old('prefecture', $user->prefecture) == '愛媛県' ? 'selected' : '' }}>愛媛県</option>
                <option value="高知県" {{ old('prefecture', $user->prefecture) == '高知県' ? 'selected' : '' }}>高知県</option>
                <option value="福岡県" {{ old('prefecture', $user->prefecture) == '福岡県' ? 'selected' : '' }}>福岡県</option>
                <option value="佐賀県" {{ old('prefecture', $user->prefecture) == '佐賀県' ? 'selected' : '' }}>佐賀県</option>
                <option value="長崎県" {{ old('prefecture', $user->prefecture) == '長崎県' ? 'selected' : '' }}>長崎県</option>
                <option value="熊本県" {{ old('prefecture', $user->prefecture) == '熊本県' ? 'selected' : '' }}>熊本県</option>
                <option value="大分県" {{ old('prefecture', $user->prefecture) == '大分県' ? 'selected' : '' }}>大分県</option>
                <option value="宮崎県" {{ old('prefecture', $user->prefecture) == '宮崎県' ? 'selected' : '' }}>宮崎県</option>
                <option value="鹿児島県" {{ old('prefecture', $user->prefecture) == '鹿児島県' ? 'selected' : '' }}>鹿児島県</option>
                <option value="沖縄県" {{ old('prefecture', $user->prefecture) == '沖縄県' ? 'selected' : '' }}>沖縄県</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('prefecture')" />
        </div>

        <!-- 体質情報セクション -->
        <div class="pt-6 border-t border-gray-200">
            <h3 class="text-base font-medium text-gray-900 mb-4">
                {{ __('体質情報') }}
            </h3>
            <p class="text-sm text-gray-600 mb-6">
                {{ __('1-5段階で感度を設定してください') }}
            </p>

            <!-- 花粉感度 -->
            <div class="mb-6">
                <x-input-label for="allergy_sensitivity" :value="__('花粉感度: ' . old('allergy_sensitivity', $user->allergy_sensitivity ?? 3) . '/5')" />
                <div class="mt-2 flex gap-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="flex-1">
                            <input 
                                type="radio" 
                                name="allergy_sensitivity" 
                                value="{{ $i }}"
                                class="sr-only peer"
                                {{ old('allergy_sensitivity', $user->allergy_sensitivity ?? 3) == $i ? 'checked' : '' }}
                            >
                            <div class="py-3 text-center rounded-lg border-2 cursor-pointer transition-all shadow-sm hover:shadow-md bg-white border-gray-200 text-gray-500
                            {{ $i == 1 ? 'peer-checked:bg-sky-400 peer-checked:border-sky-400 peer-checked:text-white' : '' }}
                            {{ $i == 2 ? 'peer-checked:bg-teal-400 peer-checked:border-teal-400 peer-checked:text-white' : '' }}
                            {{ $i == 3 ? 'peer-checked:bg-yellow-400 peer-checked:border-yellow-400 peer-checked:text-white' : '' }}
                            {{ $i == 4 ? 'peer-checked:bg-orange-400 peer-checked:border-orange-400 peer-checked:text-white' : '' }}
                            {{ $i == 5 ? 'peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white' : '' }}
                            hover:bg-gray-50">
                                {{ $i }}
                            </div>
                        </label>
                    @endfor
                </div>
                <p class="mt-1 text-xs text-gray-500">1=低い（花粉症でもない）、5=高い（重度の花粉症）</p>
                <x-input-error class="mt-2" :messages="$errors->get('allergy_sensitivity')" />
            </div>

            <!-- 寒暖差感度 -->
            <div class="mb-6">
                <x-input-label for="temperature_sensitivity" :value="__('寒暖差感度: ' . old('temperature_sensitivity', $user->temperature_sensitivity ?? 3) . '/5')" />
                <div class="mt-2 flex gap-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="flex-1">
                            <input 
                                type="radio" 
                                name="temperature_sensitivity" 
                                value="{{ $i }}"
                                class="sr-only peer"
                                {{ old('temperature_sensitivity', $user->temperature_sensitivity ?? 3) == $i ? 'checked' : '' }}
                            >
                            <div class="py-3 text-center rounded-lg border-2 cursor-pointer transition-all shadow-sm hover:shadow-md bg-white border-gray-200 text-gray-500
                            {{ $i == 1 ? 'peer-checked:bg-sky-400 peer-checked:border-sky-400 peer-checked:text-white' : '' }}
                            {{ $i == 2 ? 'peer-checked:bg-teal-400 peer-checked:border-teal-400 peer-checked:text-white' : '' }}
                            {{ $i == 3 ? 'peer-checked:bg-yellow-400 peer-checked:border-yellow-400 peer-checked:text-white' : '' }}
                            {{ $i == 4 ? 'peer-checked:bg-orange-400 peer-checked:border-orange-400 peer-checked:text-white' : '' }}
                            {{ $i == 5 ? 'peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white' : '' }}
                            hover:bg-gray-50">
                                {{ $i }}
                            </div>
                        </label>
                    @endfor
                </div>
                <p class="mt-1 text-xs text-gray-500">1=低い（寒暖差に強い）、5=高い（寒暖差に弱い）</p>
                <x-input-error class="mt-2" :messages="$errors->get('temperature_sensitivity')" />
            </div>

            <!-- 気圧感度 -->
            <div class="mb-6">
                <x-input-label for="weather_sensitivity" :value="__('気圧感度: ' . old('weather_sensitivity', $user->weather_sensitivity ?? 3) . '/5')" />
                <div class="mt-2 flex gap-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="flex-1">
                            <input 
                                type="radio" 
                                name="weather_sensitivity" 
                                value="{{ $i }}"
                                class="sr-only peer"
                                {{ old('weather_sensitivity', $user->weather_sensitivity ?? 3) == $i ? 'checked' : '' }}
                            >
                            <div class="py-3 text-center rounded-lg border-2 cursor-pointer transition-all shadow-sm hover:shadow-md bg-white border-gray-200 text-gray-500
                            {{ $i == 1 ? 'peer-checked:bg-sky-400 peer-checked:border-sky-400 peer-checked:text-white' : '' }}
                            {{ $i == 2 ? 'peer-checked:bg-teal-400 peer-checked:border-teal-400 peer-checked:text-white' : '' }}
                            {{ $i == 3 ? 'peer-checked:bg-yellow-400 peer-checked:border-yellow-400 peer-checked:text-white' : '' }}
                            {{ $i == 4 ? 'peer-checked:bg-orange-400 peer-checked:border-orange-400 peer-checked:text-white' : '' }}
                            {{ $i == 5 ? 'peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white' : '' }}
                            hover:bg-gray-50">
                                {{ $i }}
                            </div>
                        </label>
                    @endfor
                </div>
                <p class="mt-1 text-xs text-gray-500">1=低い（気圧変化に強い）、5=高い（気圧変化に弱い）</p>
                <x-input-error class="mt-2" :messages="$errors->get('weather_sensitivity')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-gray-800 hover:bg-gray-700">{{ __('設定を保存') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('保存しました') }}</p>
            @endif
        </div>
    </form>
</section>