<?php

namespace App\Http\Requests;

use App\Models\Simulation;
use Illuminate\Foundation\Http\FormRequest;

class SimulationRequest extends FormRequest
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
        return Simulation::rules();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Simulation::attributeLabels();
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareForValidation(): void
    {
        $nullable = [
            'property_id',
            'purchase_price',
            'bonus_payment_amount',
            'management_fee',
            'repair_reserve_fee',
            'parking_fee',
            'existing_annual_repayment',
            'review_interest_rate',
        ];

        foreach ($nullable as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        $defaults = [
            'bonus_payment_amount' => 0,
            'management_fee' => 0,
            'repair_reserve_fee' => 0,
            'parking_fee' => 0,
            'existing_annual_repayment' => 0,
            'review_interest_rate' => 3.0,
        ];

        foreach ($defaults as $field => $value) {
            if ($this->input($field) === null) {
                $this->merge([$field => $value]);
            }
        }
    }
}
