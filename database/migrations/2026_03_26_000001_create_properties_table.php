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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price')->comment('価格（万円）');
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable()->comment('緯度（Leaflet用）');
            $table->decimal('longitude', 10, 7)->nullable()->comment('経度（Leaflet用）');
            $table->unsignedInteger('station_minutes')->comment('駅徒歩分数');
            $table->string('evaluation', 1)->nullable()->comment('評価 (S/A/B/C)');
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index('price');
            $table->index('station_minutes');
            $table->index('evaluation');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
