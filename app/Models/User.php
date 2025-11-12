<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'prefecture',
        'allergy_sensitivity',
        'temperature_sensitivity',
        'weather_sensitivity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allergy_sensitivity' => 'int',      // 追加
            'temperature_sensitivity' => 'int',  // 追加
            'weather_sensitivity' => 'int',      // 追加
        ];
    }

    /**
     * ユーザーの鼻タイプを判定
     * 
     * @return array ['type' => string, 'icon' => string, 'description' => string]
     */
    public function getNoseType()
    {
        // 体質情報が設定されていない、またはデフォルト値（0）の場合は未設定タイプとする
        // データベースに0が保存されている可能性も考慮
        if (
            $this->allergy_sensitivity === null || $this->allergy_sensitivity === 0 ||
            $this->temperature_sensitivity === null || $this->temperature_sensitivity === 0 ||
            $this->weather_sensitivity === null || $this->weather_sensitivity === 0
        ) {
            return [
                'type' => '未設定タイプ',
                'icon' => '❓',
                'description' => '体質情報を設定すると、あなたの鼻タイプが表示されます。'
            ];
        }

        $constitution = [
            'allergy_sensitivity' => $this->allergy_sensitivity,
            'temperature_sensitivity' => $this->temperature_sensitivity,
            'weather_sensitivity' => $this->weather_sensitivity,
        ];

        return $this->determineNoseType($constitution);
    }

    /**
     * 鼻タイプ判定ロジック
     * 
     * @param array $constitution
     * @return array
     */
    private function determineNoseType($constitution)
    {
        $allergy = $constitution['allergy_sensitivity'];
        $temperature = $constitution['temperature_sensitivity'];
        $weather = $constitution['weather_sensitivity'];

        // 1. マルチアラート鼻（すべてに敏感）
        if ($allergy >= 4 && $temperature >= 4 && $weather >= 4) {
            return [
                'type' => 'マルチアラート鼻',
                'icon' => '🎯',
                'description' => '複数の要因に敏感なあなた。総合的な対策が必要です。体調管理と環境コントロールを徹底しましょう。'
            ];
        }

        // 2. 花粉ハンター鼻（アレルギーが強い）
        // 温度と天気の敏感度が低いことを明示的にチェック
        if ($allergy >= 4 && $temperature <= 3 && $weather <= 3) {
            return [
                'type' => '花粉ハンター鼻',
                'icon' => '🌸',
                'description' => '春と秋は要注意！花粉情報を毎日チェックして、外出時はマスク必須です。帰宅後はすぐに顔を洗いましょう。'
            ];
        }

        // 3. 気候センサー鼻（温度・天気に敏感）
        // アレルギーの敏感度が低いことを明示的にチェック
        if (($temperature >= 4 || $weather >= 4) && $allergy <= 3) {
            return [
                'type' => '気候センサー鼻',
                'icon' => '❄️',
                'description' => '天気の変わり目が苦手なあなた。温度差対策に重ね着を、乾燥時はマスクや加湿器が味方です。'
            ];
        }

        // 4. 敏感ノーズ（中程度の敏感さ）
        // いずれか一つでも中程度の敏感さがある場合
        if ($allergy >= 3 || $temperature >= 3 || $weather >= 3) {
            return [
                'type' => '敏感ノーズ',
                'icon' => '👃',
                'description' => '花粉やハウスダストに敏感なあなたは、常に鼻のケアを忘れずに。環境の変化に注意しましょう。'
            ];
        }

        // 5. バランス鼻（デフォルト）
        return [
            'type' => 'バランス鼻',
            'icon' => '🌟',
            'description' => 'バランスの取れた健康的な鼻。現状維持を心がけて、記録を続けましょう。'
        ];
    }

    /**
     * 鼻タイプ名だけを取得（簡易版）
     * 
     * @return string
     */
    public function getNoseTypeName()
    {
        return $this->getNoseType()['type'];
    }

    /**
     * 鼻タイプのアイコンだけを取得
     * 
     * @return string
     */
    public function getNoseTypeIcon()
    {
        return $this->getNoseType()['icon'];
    }

    /**
     * 鼻タイプの説明だけを取得
     * 
     * @return string
     */
    public function getNoseTypeDescription()
    {
        return $this->getNoseType()['description'];
    }
}