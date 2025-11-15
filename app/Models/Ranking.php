<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    /**
     * Mass Assignment（一括代入）を許可するカラムのリスト
     *
     * @var array
     */
    protected $fillable = [ 'type', 'ranking_date', 'prefecture', 'total_count',  'average_level',  'rank', ];
}