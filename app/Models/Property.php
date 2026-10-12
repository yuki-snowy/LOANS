<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

#[Fillable([
    'name',
    'price',
    'address',
    'latitude',
    'longitude',
    'station_minutes',
    'evaluation',
    'memo',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    public const EVALUATIONS = ['S', 'A', 'B', 'C'];

    /**
     * 物件のバリデーションルールを返す。
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1', 'max:999999'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'station_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'evaluation' => ['nullable', 'string', Rule::in(self::EVALUATIONS)],
            'memo' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributeLabels(): array
    {
        return [
            'name' => '物件名',
            'price' => '価格（万円）',
            'address' => '所在地',
            'latitude' => '緯度',
            'longitude' => '経度',
            'station_minutes' => '駅徒歩（分）',
            'evaluation' => '評価',
            'memo' => 'メモ',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'station_minutes' => 'integer',
        ];
    }

    /**
     * 地図表示に必要な座標が揃っているか。
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
