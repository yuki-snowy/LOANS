<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(Property::rules(), Property::imageRules());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Property::attributeLabels();
    }

    protected function prepareForValidation(): void
    {
        $nullableNumeric = [
            'latitude',
            'longitude',
            'exclusive_area',
            'built_year',
            'building_floors',
            'station_minutes',
        ];

        foreach ($nullableNumeric as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('evaluation') && $this->input('evaluation') === '') {
            $this->merge(['evaluation' => null]);
        }

        if ($this->has('memo') && $this->input('memo') === '') {
            $this->merge(['memo' => null]);
        }

        if ($this->has('postal_code') && $this->input('postal_code') === '') {
            $this->merge(['postal_code' => null]);
        }

        if ($this->has('floor_plan') && $this->input('floor_plan') === '') {
            $this->merge(['floor_plan' => null]);
        }

        if ($this->has('structure') && $this->input('structure') === '') {
            $this->merge(['structure' => null]);
        }

        if ($this->filled('postal_code')) {
            $digits = preg_replace('/\D/', '', (string) $this->input('postal_code')) ?? '';
            if (strlen($digits) === 7) {
                $this->merge([
                    'postal_code' => substr($digits, 0, 3).'-'.substr($digits, 3),
                ]);
            }
        }

        if (! $this->has('delete_images')) {
            $this->merge(['delete_images' => []]);
        }
    }
}
