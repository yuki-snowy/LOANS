<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_type', 32)->nullable()->after('name')->comment('物件種目');
        });

        $properties = DB::table('properties')->select('id', 'station_minutes', 'building_floors', 'built_year')->get();

        foreach ($properties as $property) {
            $minutes = (int) $property->station_minutes;
            if ($minutes > 30) {
                $minutes = 99;
            } elseif ($minutes > 0) {
                $minutes = (int) (ceil($minutes / 5) * 5);
            } else {
                $minutes = 5;
            }

            $floors = (int) ($property->building_floors ?? 1);
            if ($floors <= 1) {
                $floors = 1;
            } elseif ($floors === 2) {
                $floors = 2;
            } else {
                $floors = 3;
            }

            $currentYear = (int) date('Y');
            $type = 'used_mansion';
            if ($property->built_year !== null && (int) $property->built_year >= $currentYear) {
                $type = 'new';
            }

            DB::table('properties')->where('id', $property->id)->update([
                'station_minutes' => $minutes,
                'building_floors' => $floors,
                'property_type' => $type,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('property_type');
        });
    }
};
