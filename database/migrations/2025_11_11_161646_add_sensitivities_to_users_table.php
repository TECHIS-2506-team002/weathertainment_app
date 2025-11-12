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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('allergy_sensitivity')->default(3)->after('prefecture');
            $table->integer('temperature_sensitivity')->default(3)->after('allergy_sensitivity');
            $table->integer('weather_sensitivity')->default(3)->after('temperature_sensitivity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allergy_sensitivity', 'temperature_sensitivity', 'weather_sensitivity']);
        });
    }
};