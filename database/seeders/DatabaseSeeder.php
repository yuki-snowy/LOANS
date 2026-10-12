<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Property::factory()->create([
            'name' => 'パークタワー青山 1203号室',
            'price' => 9800,
            'address' => '東京都港区南青山2-5-17',
            'latitude' => 35.6656820,
            'longitude' => 139.7120880,
            'station_minutes' => 5,
            'evaluation' => 'S',
            'memo' => '角部屋・南向き。眺望と共用部の質が高い。',
        ]);

        Property::factory()->create([
            'name' => 'シティハウス目黒 501号室',
            'price' => 6200,
            'address' => '東京都目黒区下目黒1-1-1',
            'latitude' => 35.6335600,
            'longitude' => 139.7157800,
            'station_minutes' => 7,
            'evaluation' => 'A',
            'memo' => '駅近。リフォーム済で即入居可。',
        ]);

        Property::factory()->create([
            'name' => 'プラウド世田谷 802号室',
            'price' => 5400,
            'address' => '東京都世田谷区桜丘2-3-4',
            'latitude' => null,
            'longitude' => null,
            'station_minutes' => 12,
            'evaluation' => 'B',
            'memo' => '座標未設定のサンプル。',
        ]);
    }
}
