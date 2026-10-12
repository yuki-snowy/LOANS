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
        return Property::rules();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Property::attributeLabels();
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareForValidation(): void
    {
        $nullableNumeric = ['latitude', 'longitude'];

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

        if ($this->filled('postal_code')) {
            $digits = preg_replace('/\D/', '', (string) $this->input('postal_code')) ?? '';
            if (strlen($digits) === 7) {
                $this->merge([
                    'postal_code' => substr($digits, 0, 3).'-'.substr($digits, 3),
                ]);
            }
        }
    }
}
