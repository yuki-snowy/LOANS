<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Property::factory()->create([
            'name' => 'パークタワー青山 1203号室',
            'property_type' => 'used_mansion',
            'price' => 9800,
            'postal_code' => '107-0062',
            'prefecture' => '東京都',
            'address' => '港区南青山2-5-17',
            'latitude' => 35.6656820,
            'longitude' => 139.7120880,
            'station_minutes' => 5,
            'floor_plan' => '3LDK',
            'built_year' => 2015,
            'exclusive_area' => 78.45,
            'structure' => 'RC造',
            'building_floors' => 3,
            'evaluation' => 'S',
            'memo' => '角部屋・南向き。眺望と共用部の質が高い。',
        ]);

        Property::factory()->create([
            'name' => 'シティハウス目黒 501号室',
            'property_type' => 'used_mansion',
            'price' => 6200,
            'postal_code' => '153-0064',
            'prefecture' => '東京都',
            'address' => '目黒区下目黒1-1-1',
            'latitude' => 35.6335600,
            'longitude' => 139.7157800,
            'station_minutes' => 10,
            'floor_plan' => '2LDK',
            'built_year' => 2008,
            'exclusive_area' => 58.20,
            'structure' => 'SRC造',
            'building_floors' => 3,
            'evaluation' => 'A',
            'memo' => '駅近。リフォーム済で即入居可。',
        ]);

        Property::factory()->create([
            'name' => 'プラウド世田谷 802号室',
            'property_type' => 'used_house',
            'price' => 5400,
            'postal_code' => '156-0051',
            'prefecture' => '東京都',
            'address' => '世田谷区桜丘2-3-4',
            'latitude' => null,
            'longitude' => null,
            'station_minutes' => 15,
            'floor_plan' => '2DK',
            'built_year' => 1999,
            'exclusive_area' => 49.80,
            'structure' => '鉄骨造',
            'building_floors' => 2,
            'evaluation' => 'B',
            'memo' => '座標未設定のサンプル。',
        ]);
    }
}
