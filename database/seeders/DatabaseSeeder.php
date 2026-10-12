<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\SimulationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const IMAGES_PER_PROPERTY = 10;

    public function run(): void
    {
        $simulationService = app(SimulationService::class);
        $year = (int) date('Y');

        $properties = [
            [
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
                'memo' => 'ハイエンド中古マンション。',
            ],
            [
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
                'memo' => '駅近・リフォーム済。',
            ],
            [
                'name' => '新築分譲 渋谷桜丘レジデンス 302',
                'property_type' => 'new',
                'price' => 11800,
                'postal_code' => '150-0031',
                'prefecture' => '東京都',
                'address' => '渋谷区桜丘町20-1',
                'latitude' => 35.6559800,
                'longitude' => 139.7005200,
                'station_minutes' => 5,
                'floor_plan' => '3LDK',
                'built_year' => $year,
                'exclusive_area' => 82.10,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'S',
                'memo' => '新築種目・築浅。',
            ],
            [
                'name' => '世田谷桜丘の戸建て',
                'property_type' => 'used_house',
                'price' => 7480,
                'postal_code' => '156-0051',
                'prefecture' => '東京都',
                'address' => '世田谷区桜丘2-3-4',
                'latitude' => 35.6401200,
                'longitude' => 139.6238800,
                'station_minutes' => 15,
                'floor_plan' => '4LDK',
                'built_year' => 1999,
                'exclusive_area' => 98.50,
                'structure' => '木造',
                'building_floors' => 2,
                'evaluation' => 'B',
                'memo' => '中古一戸建て・2階建。',
            ],
            [
                'name' => '練馬土地（建築条件なし）',
                'property_type' => 'land',
                'price' => 4200,
                'postal_code' => '176-0002',
                'prefecture' => '東京都',
                'address' => '練馬区桜台3-12-8',
                'latitude' => 35.7432100,
                'longitude' => 139.6623400,
                'station_minutes' => 20,
                'floor_plan' => 'ワンルーム',
                'built_year' => 1950,
                'exclusive_area' => 85.00,
                'structure' => 'その他',
                'building_floors' => 1,
                'evaluation' => 'C',
                'memo' => '土地種目・1階建相当。',
            ],
            [
                'name' => '川崎市中原区 駅遠リノベマンション',
                'property_type' => 'used_mansion',
                'price' => 3180,
                'postal_code' => '211-0004',
                'prefecture' => '神奈川県',
                'address' => '川崎市中原区新丸子東1-2-3',
                'latitude' => 35.5804500,
                'longitude' => 139.6621100,
                'station_minutes' => 99,
                'floor_plan' => '1LDK',
                'built_year' => 1988,
                'exclusive_area' => 42.30,
                'structure' => '軽量鉄骨造',
                'building_floors' => 3,
                'evaluation' => null,
                'memo' => '駅徒歩30分以上・評価未設定。',
            ],
            [
                'name' => '横浜みなとみらい サービスアパート',
                'property_type' => 'other',
                'price' => 5400,
                'postal_code' => '220-0012',
                'prefecture' => '神奈川県',
                'address' => '横浜市西区みなとみらい1-1-1',
                'latitude' => 35.4559800,
                'longitude' => 139.6312800,
                'station_minutes' => 25,
                'floor_plan' => '2DK',
                'built_year' => 2005,
                'exclusive_area' => 51.00,
                'structure' => '鉄骨造',
                'building_floors' => 2,
                'evaluation' => 'B',
                'memo' => 'その他種目・駅徒歩25分。',
            ],
            [
                'name' => '千葉ニュータウン ファミリー向け',
                'property_type' => 'used_mansion',
                'price' => 2890,
                'postal_code' => '270-1350',
                'prefecture' => '千葉県',
                'address' => '印西市中央南1-1',
                'latitude' => 35.7965400,
                'longitude' => 140.1458200,
                'station_minutes' => 10,
                'floor_plan' => '5LDK以上',
                'built_year' => 2010,
                'exclusive_area' => 110.25,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'A',
                'memo' => '広い間取り・千葉エリア。',
            ],
            [
                'name' => '新築戸建 府中白糸台',
                'property_type' => 'new',
                'price' => 5680,
                'postal_code' => '183-0011',
                'prefecture' => '東京都',
                'address' => '府中市白糸台1-2-3',
                'latitude' => 35.6661200,
                'longitude' => 139.5123400,
                'station_minutes' => 15,
                'floor_plan' => '4LDK',
                'built_year' => $year,
                'exclusive_area' => 95.80,
                'structure' => '木造',
                'building_floors' => 2,
                'evaluation' => 'A',
                'memo' => '新築戸建・2階建。',
            ],
            [
                'name' => '中野新橋 ワンルーム投資向き',
                'property_type' => 'used_mansion',
                'price' => 1980,
                'postal_code' => '164-0011',
                'prefecture' => '東京都',
                'address' => '中野区中央2-8-9',
                'latitude' => 35.6974500,
                'longitude' => 139.6712300,
                'station_minutes' => 5,
                'floor_plan' => 'ワンルーム',
                'built_year' => 1995,
                'exclusive_area' => 22.40,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'C',
                'memo' => 'コンパクト・駅徒歩5分。',
            ],
            [
                'name' => 'さいたま新都心タワー 1501',
                'property_type' => 'used_mansion',
                'price' => 4550,
                'postal_code' => '330-0854',
                'prefecture' => '埼玉県',
                'address' => 'さいたま市大宮区桜木町1-10',
                'latitude' => 35.8932100,
                'longitude' => 139.6334500,
                'station_minutes' => 10,
                'floor_plan' => '2LDK',
                'built_year' => 2018,
                'exclusive_area' => 65.70,
                'structure' => 'SRC造',
                'building_floors' => 3,
                'evaluation' => 'A',
                'memo' => '埼玉県・タワーマンション。',
            ],
            [
                'name' => '武蔵野吉祥寺 中古一戸建て',
                'property_type' => 'used_house',
                'price' => 8980,
                'postal_code' => '180-0003',
                'prefecture' => '東京都',
                'address' => '武蔵野市吉祥寺南町1-5-6',
                'latitude' => 35.7001200,
                'longitude' => 139.5789000,
                'station_minutes' => 20,
                'floor_plan' => '3LDK',
                'built_year' => 1985,
                'exclusive_area' => 88.00,
                'structure' => '木造',
                'building_floors' => 2,
                'evaluation' => 'B',
                'memo' => '築年が古い戸建サンプル。',
            ],
            [
                'name' => '座標未設定サンプル（品川）',
                'property_type' => 'used_mansion',
                'price' => 7200,
                'postal_code' => null,
                'prefecture' => '東京都',
                'address' => '品川区大崎1-1-1',
                'latitude' => null,
                'longitude' => null,
                'station_minutes' => 5,
                'floor_plan' => '1DK',
                'built_year' => 2012,
                'exclusive_area' => 38.60,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'B',
                'memo' => '郵便番号なし・地図ピンなし（画像は10枚あり）。',
            ],
            [
                'name' => '八王子土地 広々敷地',
                'property_type' => 'land',
                'price' => 2650,
                'postal_code' => '192-0082',
                'prefecture' => '東京都',
                'address' => '八王子市東町1-2',
                'latitude' => 35.6557800,
                'longitude' => 139.3389000,
                'station_minutes' => 99,
                'floor_plan' => 'ワンルーム',
                'built_year' => 1950,
                'exclusive_area' => 120.00,
                'structure' => 'その他',
                'building_floors' => 1,
                'evaluation' => null,
                'memo' => '土地・駅徒歩30分以上。',
            ],
            [
                'name' => '松戸五香 郊外ファミリーマンション',
                'property_type' => 'used_mansion',
                'price' => 2490,
                'postal_code' => '270-2214',
                'prefecture' => '千葉県',
                'address' => '松戸市五香1-3-5',
                'latitude' => 35.7901200,
                'longitude' => 139.9654300,
                'station_minutes' => 15,
                'floor_plan' => '3DK',
                'built_year' => 2001,
                'exclusive_area' => 68.90,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'C',
                'memo' => '郊外・価格控えめサンプル。',
            ],
        ];

        $created = [];

        foreach ($properties as $data) {
            $property = Property::factory()->create($data);
            $this->attachSeedImages($property, self::IMAGES_PER_PROPERTY);
            $created[] = $property;
        }

        $scenarios = [
            // 安全寄り
            ['idx' => 0, 'borrow' => 7000, 'rate' => 0.55, 'years' => 35, 'bonus' => 20, 'mgmt' => 18000, 'repair' => 12000, 'park' => 30000, 'income' => 1500, 'emp' => 'full_time', 'service' => 12, 'existing' => 0],
            ['idx' => 1, 'borrow' => 4500, 'rate' => 0.70, 'years' => 35, 'bonus' => 0, 'mgmt' => 12000, 'repair' => 8000, 'park' => 0, 'income' => 900, 'emp' => 'full_time', 'service' => 8, 'existing' => 0],
            ['idx' => 2, 'borrow' => 8000, 'rate' => 0.65, 'years' => 35, 'bonus' => 30, 'mgmt' => 22000, 'repair' => 15000, 'park' => 35000, 'income' => 1600, 'emp' => 'full_time', 'service' => 15, 'existing' => 0],
            ['idx' => 7, 'borrow' => 2200, 'rate' => 0.50, 'years' => 35, 'bonus' => 0, 'mgmt' => 10000, 'repair' => 6000, 'park' => 0, 'income' => 750, 'emp' => 'full_time', 'service' => 5, 'existing' => 0],
            ['idx' => 10, 'borrow' => 3500, 'rate' => 0.60, 'years' => 35, 'bonus' => 10, 'mgmt' => 14000, 'repair' => 9000, 'park' => 15000, 'income' => 850, 'emp' => 'full_time', 'service' => 7, 'existing' => 0],
            // 注意寄り
            ['idx' => 3, 'borrow' => 6000, 'rate' => 0.90, 'years' => 30, 'bonus' => 10, 'mgmt' => 0, 'repair' => 0, 'park' => 0, 'income' => 780, 'emp' => 'self_employed', 'service' => 6, 'existing' => 20],
            ['idx' => 4, 'borrow' => 3800, 'rate' => 1.00, 'years' => 30, 'bonus' => 0, 'mgmt' => 0, 'repair' => 0, 'park' => 0, 'income' => 620, 'emp' => 'contract', 'service' => 3, 'existing' => 0],
            ['idx' => 8, 'borrow' => 4800, 'rate' => 0.85, 'years' => 35, 'bonus' => 0, 'mgmt' => 0, 'repair' => 0, 'park' => 0, 'income' => 700, 'emp' => 'full_time', 'service' => 4, 'existing' => 40],
            ['idx' => 11, 'borrow' => 7000, 'rate' => 1.10, 'years' => 30, 'bonus' => 20, 'mgmt' => 0, 'repair' => 0, 'park' => 0, 'income' => 900, 'emp' => 'self_employed', 'service' => 10, 'existing' => 30],
            ['idx' => 14, 'borrow' => 2300, 'rate' => 0.95, 'years' => 25, 'bonus' => 0, 'mgmt' => 11000, 'repair' => 7000, 'park' => 5000, 'income' => 520, 'emp' => 'contract', 'service' => 2, 'existing' => 10],
            // 危険寄り
            ['idx' => 5, 'borrow' => 3000, 'rate' => 1.50, 'years' => 25, 'bonus' => 0, 'mgmt' => 9000, 'repair' => 7000, 'park' => 10000, 'income' => 420, 'emp' => 'other', 'service' => 1, 'existing' => 60],
            ['idx' => 6, 'borrow' => 5000, 'rate' => 1.30, 'years' => 30, 'bonus' => 40, 'mgmt' => 15000, 'repair' => 10000, 'park' => 20000, 'income' => 500, 'emp' => 'contract', 'service' => 1, 'existing' => 80],
            ['idx' => 9, 'borrow' => 1800, 'rate' => 1.80, 'years' => 20, 'bonus' => 0, 'mgmt' => 8000, 'repair' => 5000, 'park' => 0, 'income' => 350, 'emp' => 'other', 'service' => 0, 'existing' => 40],
            ['idx' => 12, 'borrow' => 6500, 'rate' => 1.40, 'years' => 35, 'bonus' => 0, 'mgmt' => 16000, 'repair' => 11000, 'park' => 25000, 'income' => 480, 'emp' => 'full_time', 'service' => 2, 'existing' => 100],
            // 物件未紐づけ
            ['idx' => null, 'purchase_price' => 4500, 'borrow' => 4000, 'rate' => 0.70, 'years' => 35, 'bonus' => 0, 'mgmt' => 15000, 'repair' => 10000, 'park' => 20000, 'income' => 1000, 'emp' => 'full_time', 'service' => 10, 'existing' => 0],
        ];

        foreach ($scenarios as $scenario) {
            $property = $scenario['idx'] === null ? null : $created[$scenario['idx']];

            $simulationService->create([
                'property_id' => $property?->id,
                'purchase_price' => $scenario['purchase_price'] ?? $property?->price,
                'borrowing_amount' => $scenario['borrow'],
                'interest_rate' => $scenario['rate'],
                'repayment_years' => $scenario['years'],
                'bonus_payment_amount' => $scenario['bonus'],
                'management_fee' => $scenario['mgmt'],
                'repair_reserve_fee' => $scenario['repair'],
                'parking_fee' => $scenario['park'],
                'annual_income' => $scenario['income'],
                'employment_type' => $scenario['emp'],
                'years_of_service' => $scenario['service'],
                'existing_annual_repayment' => $scenario['existing'],
            ]);
        }
    }

    private function attachSeedImages(Property $property, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $fixtures = collect(range(1, 10))
            ->map(fn (int $i) => database_path('seeders/fixtures/property_'.$i.'.jpg'))
            ->filter(fn (string $path) => File::exists($path))
            ->values();

        if ($fixtures->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $source = $fixtures[$i % $fixtures->count()];
            $relative = 'properties/'.$property->id.'/seed-'.($i + 1).'.jpg';

            Storage::disk('public')->put($relative, File::get($source));

            PropertyImage::query()->create([
                'property_id' => $property->id,
                'path' => $relative,
                'sort_order' => $i,
            ]);
        }
    }
}
