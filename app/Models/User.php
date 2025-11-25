<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'prefecture',
        'allergy_sensitivity',
        'temperature_sensitivity',
        'weather_sensitivity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allergy_sensitivity' => 'int',
            'temperature_sensitivity' => 'int',
            'weather_sensitivity' => 'int',
        ];
    }

    /**
     * ユーザーの鼻タイプを判定
     * 
     * @return array ['type' => string, 'icon' => string, 'description' => string]
     */
    public function getNoseType(): array
    {
        // 体質情報が未設定の場合は、診断不可とする
        if (
            $this->allergy_sensitivity === null || $this->allergy_sensitivity === 0 ||
            $this->temperature_sensitivity === null || $this->temperature_sensitivity === 0 ||
            $this->weather_sensitivity === null || $this->weather_sensitivity === 0
        ) {
            return [
                'type' => '未設定',
                'icon' => '❓',
                'description' => 'プロフィール設定であなたの体質を教えると、鼻タイプが診断されます。'
            ];
        }

        // privateメソッドに処理を委譲
        return $this->determineNoseType(
            $this->allergy_sensitivity,
            $this->temperature_sensitivity,
            $this->weather_sensitivity
        );
    }

    /**
     * 鼻タイプ判定ロジック（あなたの新しいロジックに完全準拠）
     * 
     * @param int $allergyLevel
     * @param int $temperatureSensitivity
     * @param int $weatherSensitivity
     * @return array
     */
    private function determineNoseType(int $allergyLevel, int $temperatureSensitivity, int $weatherSensitivity): array
    {
        // 1. 花粉戦士
        if ($allergyLevel >= 4) {
            return [
                'type' => '花粉戦士',
                'icon' => '🌸', // emojiをiconキーに変更
                'description' => '花粉に敏感すぎる戦士。春は修行の季節。',
            ];
        }

        // 2. 寒暖差ナイト
        if ($temperatureSensitivity >= 4) {
            return [
                'type' => '寒暖差ナイト',
                'icon' => '🌡️',
                'description' => '気温の変化に敏感な騎士。季節の変わり目は敵地。',
            ];
        }

        // 3. 気圧侍
        if ($weatherSensitivity >= 4) {
            return [
                'type' => '気圧侍',
                'icon' => '🌪️',
                'description' => '気圧変化に敏感な侍。低気圧は宿敵。',
            ];
        }

        // 平均値を計算
        $average = ($allergyLevel + $temperatureSensitivity + $weatherSensitivity) / 3;

        // 4. 鼻の貴族
        if ($average <= 2) {
            return [
                'type' => '鼻の貴族',
                'icon' => '👑',
                'description' => 'くしゃみとは無縁の優雅な貴族。羨ましい。',
            ];
        }

        // 5. 平均的な鼻 (上記以外)
        return [
            'type' => '平均的な鼻',
            'icon' => '👃',
            'description' => '世の中の多くの人と同じ。普通が一番。',
        ];
    }
}
