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

    public function run(): void
    {
        $simulationService = app(SimulationService::class);

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
                'memo' => '角部屋・南向き。デバッグ用ハイエンド物件。',
                'images' => 3,
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
                'memo' => '駅近・リフォーム済。画像2枚のサンプル。',
                'images' => 2,
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
                'built_year' => (int) date('Y'),
                'exclusive_area' => 82.10,
                'structure' => 'RC造',
                'building_floors' => 3,
                'evaluation' => 'S',
                'memo' => '新築種目・築浅の確認用。',
                'images' => 3,
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
                'memo' => '中古一戸建て・2階建の確認用。',
                'images' => 3,
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
                'memo' => '土地種目・1階建・駅徒歩20分の確認用。',
                'images' => 1,
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
                'memo' => '駅徒歩30分以上・評価未設定の確認用。',
                'images' => 3,
            ],
            [
                'name' => '座標未設定サンプル（横浜）',
                'property_type' => 'other',
                'price' => 5400,
                'postal_code' => null,
                'prefecture' => '神奈川県',
                'address' => '横浜市西区みなとみらい1-1-1',
                'latitude' => null,
                'longitude' => null,
                'station_minutes' => 25,
                'floor_plan' => '2DK',
                'built_year' => 2005,
                'exclusive_area' => 51.00,
                'structure' => '鉄骨造',
                'building_floors' => 2,
                'evaluation' => 'B',
                'memo' => '郵便番号なし・地図ピンなし・画像なし。',
                'images' => 0,
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
                'memo' => '広い間取り・地方物件の地図確認用。',
                'images' => 2,
            ],
        ];

        $created = [];

        foreach ($properties as $data) {
            $imageCount = (int) ($data['images'] ?? 0);
            unset($data['images']);

            $property = Property::factory()->create($data);
            $this->attachSeedImages($property, $imageCount);
            $created[] = $property;
        }

        // 審査シグナル網羅: 安全 / 注意 / 危険 + 条件バリエーション
        $scenarios = [
            [
                'property' => $created[0],
                'borrowing_amount' => 7000,
                'interest_rate' => 0.6,
                'repayment_years' => 35,
                'bonus_payment_amount' => 20,
                'management_fee' => 18000,
                'repair_reserve_fee' => 12000,
                'parking_fee' => 30000,
                'annual_income' => 1400,
                'employment_type' => 'full_time',
                'years_of_service' => 12,
                'existing_annual_repayment' => 0,
            ],
            [
                'property' => $created[1],
                'borrowing_amount' => 5000,
                'interest_rate' => 0.8,
                'repayment_years' => 35,
                'bonus_payment_amount' => 0,
                'management_fee' => 12000,
                'repair_reserve_fee' => 8000,
                'parking_fee' => 0,
                'annual_income' => 700,
                'employment_type' => 'full_time',
                'years_of_service' => 6,
                'existing_annual_repayment' => 30,
            ],
            [
                'property' => $created[2],
                'borrowing_amount' => 10000,
                'interest_rate' => 1.2,
                'repayment_years' => 35,
                'bonus_payment_amount' => 50,
                'management_fee' => 22000,
                'repair_reserve_fee' => 15000,
                'parking_fee' => 35000,
                'annual_income' => 650,
                'employment_type' => 'contract',
                'years_of_service' => 2,
                'existing_annual_repayment' => 80,
            ],
            [
                'property' => $created[3],
                'borrowing_amount' => 5500,
                'interest_rate' => 0.9,
                'repayment_years' => 30,
                'bonus_payment_amount' => 10,
                'management_fee' => 0,
                'repair_reserve_fee' => 0,
                'parking_fee' => 0,
                'annual_income' => 900,
                'employment_type' => 'self_employed',
                'years_of_service' => 8,
                'existing_annual_repayment' => 0,
            ],
            [
                'property' => $created[5],
                'borrowing_amount' => 2800,
                'interest_rate' => 1.5,
                'repayment_years' => 25,
                'bonus_payment_amount' => 0,
                'management_fee' => 9000,
                'repair_reserve_fee' => 7000,
                'parking_fee' => 10000,
                'annual_income' => 480,
                'employment_type' => 'other',
                'years_of_service' => 1,
                'existing_annual_repayment' => 50,
            ],
            [
                'property' => $created[7],
                'borrowing_amount' => 2200,
                'interest_rate' => 0.5,
                'repayment_years' => 35,
                'bonus_payment_amount' => 0,
                'management_fee' => 10000,
                'repair_reserve_fee' => 6000,
                'parking_fee' => 0,
                'annual_income' => 750,
                'employment_type' => 'full_time',
                'years_of_service' => 4,
                'existing_annual_repayment' => 0,
            ],
            // 物件未紐づけの単独試算
            [
                'property' => null,
                'purchase_price' => 4500,
                'borrowing_amount' => 4000,
                'interest_rate' => 0.7,
                'repayment_years' => 35,
                'bonus_payment_amount' => 0,
                'management_fee' => 15000,
                'repair_reserve_fee' => 10000,
                'parking_fee' => 20000,
                'annual_income' => 1000,
                'employment_type' => 'full_time',
                'years_of_service' => 10,
                'existing_annual_repayment' => 0,
            ],
        ];

        foreach ($scenarios as $scenario) {
            $property = $scenario['property'] ?? null;
            unset($scenario['property']);

            $simulationService->create([
                ...$scenario,
                'property_id' => $property?->id,
                'purchase_price' => $scenario['purchase_price'] ?? $property?->price,
            ]);
        }
    }

    private function attachSeedImages(Property $property, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $fixtures = collect(range(1, 5))
            ->map(fn (int $i) => database_path('seeders/fixtures/property_'.$i.'.jpg'))
            ->filter(fn (string $path) => File::exists($path))
            ->values();

        if ($fixtures->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $source = $fixtures[$i % $fixtures->count()];
            $filename = sprintf('seed-%d.jpg', $i + 1);
            $relative = 'properties/'.$property->id.'/'.$filename;

            Storage::disk('public')->put($relative, File::get($source));

            PropertyImage::query()->create([
                'property_id' => $property->id,
                'path' => $relative,
                'sort_order' => $i,
            ]);
        }
    }
}
