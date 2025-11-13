<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sneeze_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーID (必須)
            $table->integer('level')->comment('辛さレベル 1-5'); // くしゃみの辛さレベル
            $table->integer('count')->default(1)->comment('くしゃみ回数'); // くしゃみ回数
            $table->text('memo')->nullable()->comment('メモ（任意）'); // メモ
            $table->string('prefecture')->nullable()->comment('記録時の都道府県'); // 記録時の都道府県（位置情報から取得する場合など）
            $table->timestamps(); // created_at, updated_at
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sneeze_logs');
    }
};
