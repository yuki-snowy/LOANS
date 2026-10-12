<?php

namespace App\Models;

use App\Support\Prefectures;
use App\Support\PropertyOptions;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\Rule;

#[Fillable([
    'name',
    'property_type',
    'price',
    'postal_code',
    'prefecture',
    'address',
    'latitude',
    'longitude',
    'station_minutes',
    'floor_plan',
    'built_year',
    'exclusive_area',
    'structure',
    'building_floors',
    'evaluation',
    'memo',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    public const EVALUATIONS = ['S', 'A', 'B', 'C'];

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'name' => ['required', 'string', 'max:255'],
            'property_type' => ['required', 'string', Rule::in(array_keys(PropertyOptions::PROPERTY_TYPES))],
            'price' => ['required', 'integer', 'min:1', 'max:999999'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{3}-?\d{4}$/'],
            'prefecture' => ['required', 'string', Rule::in(Prefectures::ALL)],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'station_minutes' => ['required', 'integer', Rule::in(PropertyOptions::stationMinutes())],
            'floor_plan' => ['required', 'string', Rule::in(PropertyOptions::FLOOR_PLANS)],
            'built_year' => ['required', 'integer', 'min:1950', 'max:'.$currentYear],
            'exclusive_area' => ['required', 'numeric', 'min:1', 'max:9999.99'],
            'structure' => ['required', 'string', Rule::in(PropertyOptions::STRUCTURES)],
            'building_floors' => ['required', 'integer', Rule::in(array_keys(PropertyOptions::BUILDING_FLOORS))],
            'evaluation' => ['nullable', 'string', Rule::in(self::EVALUATIONS)],
            'memo' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function imageRules(): array
    {
        return [
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:property_images,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributeLabels(): array
    {
        return [
            'name' => '物件名',
            'property_type' => '物件種目',
            'price' => '価格（万円）',
            'postal_code' => '郵便番号',
            'prefecture' => '都道府県',
            'address' => '住所',
            'latitude' => '緯度',
            'longitude' => '経度',
            'station_minutes' => '駅徒歩',
            'floor_plan' => '間取り',
            'built_year' => '築年',
            'exclusive_area' => '専有面積（㎡）',
            'structure' => '構造',
            'building_floors' => '階建て',
            'evaluation' => '評価',
            'memo' => 'メモ',
            'images' => '画像',
            'images.*' => '画像',
            'delete_images' => '削除画像',
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
            'built_year' => 'integer',
            'exclusive_area' => 'decimal:2',
            'building_floors' => 'integer',
        ];
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function formattedPostalCode(): ?string
    {
        if ($this->postal_code === null || $this->postal_code === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->postal_code) ?? '';

        if (strlen($digits) !== 7) {
            return $this->postal_code;
        }

        return substr($digits, 0, 3).'-'.substr($digits, 3);
    }

    public function fullAddress(): string
    {
        $postal = $this->formattedPostalCode();
        $line = trim(($this->prefecture ?? '').$this->address);

        return $postal ? '〒'.$postal.' '.$line : $line;
    }

    public function propertyTypeLabel(): string
    {
        return PropertyOptions::PROPERTY_TYPES[$this->property_type] ?? '—';
    }

    public function stationMinutesLabel(): string
    {
        return PropertyOptions::stationMinutesLabel((int) $this->station_minutes);
    }

    public function buildingFloorsLabel(): string
    {
        return PropertyOptions::buildingFloorsLabel((int) ($this->building_floors ?? 1));
    }

    public function builtYearLabel(): string
    {
        if (! $this->built_year) {
            return '—';
        }

        return PropertyOptions::builtYearLabel((int) $this->built_year);
    }

    public function structureLabel(): string
    {
        $parts = array_filter([
            $this->structure,
            $this->building_floors ? $this->buildingFloorsLabel() : null,
        ]);

        return $parts !== [] ? implode(' / ', $parts) : '—';
    }

    /**
     * @return HasOne<PropertyImage, $this>
     */
    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->ofMany([
            'sort_order' => 'min',
            'id' => 'min',
        ]);
    }

    /**
     * @return HasMany<PropertyImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return HasMany<Simulation, $this>
     */
    public function simulations(): HasMany
    {
        return $this->hasMany(Simulation::class);
    }
}
