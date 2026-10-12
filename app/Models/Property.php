<?php

namespace App\Models;

use App\Support\Prefectures;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

#[Fillable([
    'name',
    'price',
    'postal_code',
    'prefecture',
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
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1', 'max:999999'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{3}-?\d{4}$/'],
            'prefecture' => ['required', 'string', Rule::in(Prefectures::ALL)],
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
            'postal_code' => '郵便番号',
            'prefecture' => '都道府県',
            'address' => '住所',
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

    /**
     * @return HasMany<Simulation, $this>
     */
    public function simulations(): HasMany
    {
        return $this->hasMany(Simulation::class);
    }
}
