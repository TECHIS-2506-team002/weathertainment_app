<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/..._create_rankings_table.php の up メソッド

    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('sneeze_count'); // くしゃみ関連のランキングタイプ
            $table->date('ranking_date');
            $table->string('prefecture');
            $table->integer('total_count'); // 合計くしゃみ回数
            $table->decimal('average_level', 3, 1); // 平均辛さレベル（小数点1桁まで）
            $table->integer('rank');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
