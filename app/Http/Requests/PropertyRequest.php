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
    }
}
