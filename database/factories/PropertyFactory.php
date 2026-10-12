<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'パークタワー青山',
                'グランドメゾン渋谷',
                'シティハウス目黒',
                'プラウド世田谷',
                'ブリリア中目黒',
            ]).' '.fake()->buildingNumber().'号室',
            'price' => fake()->numberBetween(3000, 15000),
            'address' => '東京都'.fake()->randomElement([
                '港区', '渋谷区', '目黒区', '世田谷区', '新宿区',
            ]).fake()->streetAddress(),
            'latitude' => fake()->optional(0.8)->latitude(35.6, 35.75),
            'longitude' => fake()->optional(0.8)->longitude(139.65, 139.8),
            'station_minutes' => fake()->numberBetween(1, 20),
            'evaluation' => fake()->optional(0.7)->randomElement(Property::EVALUATIONS),
            'memo' => fake()->optional(0.5)->realText(80),
        ];
    }
}
