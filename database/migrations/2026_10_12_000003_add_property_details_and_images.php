<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('floor_plan', 32)->nullable()->after('station_minutes')->comment('間取り');
            $table->unsignedSmallInteger('built_year')->nullable()->after('floor_plan')->comment('築年');
            $table->decimal('exclusive_area', 8, 2)->nullable()->after('built_year')->comment('専有面積（㎡）');
            $table->string('structure', 32)->nullable()->after('exclusive_area')->comment('構造');
            $table->unsignedTinyInteger('building_floors')->nullable()->after('structure')->comment('階建て');
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_images');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'floor_plan',
                'built_year',
                'exclusive_area',
                'structure',
                'building_floors',
            ]);
        });
    }
};
