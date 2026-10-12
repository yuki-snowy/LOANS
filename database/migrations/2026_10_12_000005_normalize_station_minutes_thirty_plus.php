<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 駅徒歩 30分以降は 99（30分以上）に統一
        DB::table('properties')
            ->where('station_minutes', '>=', 30)
            ->where('station_minutes', '!=', 99)
            ->update(['station_minutes' => 99]);
    }

    public function down(): void
    {
        // 集約値の分解は不可のため no-op
    }
};
