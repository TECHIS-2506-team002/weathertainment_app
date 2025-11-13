<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SneezeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'count',
        'memo',
        'prefecture',
    ];

    /**
     * このくしゃみログが属するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
