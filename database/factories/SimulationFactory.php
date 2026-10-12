<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Simulation;
use App\Services\LoanCalculatorService;
use App\Services\SimulationService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Simulation>
 */
class SimulationFactory extends Factory
{
    protected $model = Simulation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $input = [
            'property_id' => null,
            'borrowing_amount' => 3000,
            'interest_rate' => 0.5,
            'repayment_years' => 35,
            'bonus_payment_amount' => 0,
            'management_fee' => 12000,
            'repair_reserve_fee' => 8000,
            'parking_fee' => 0,
            'annual_income' => 800,
            'employment_type' => 'full_time',
            'years_of_service' => 5,
            'existing_annual_repayment' => 0,
            'review_interest_rate' => LoanCalculatorService::DEFAULT_REVIEW_RATE_PERCENT,
        ];

        $preview = app(SimulationService::class)->preview($input);

        return [
            ...$input,
            'monthly_payment_yen' => $preview['repayment']['monthly_payment_yen'],
            'total_repayment_yen' => $preview['repayment']['total_repayment_yen'],
            'total_interest_yen' => $preview['repayment']['total_interest_yen'],
            'actual_monthly_outflow_yen' => $preview['repayment']['actual_monthly_outflow_yen'],
            'annual_loan_repayment_yen' => $preview['repayment']['annual_loan_repayment_yen'],
            'repayment_ratio_percent' => $preview['screening']['repayment_ratio_percent'],
            'stress_repayment_ratio_percent' => $preview['screening']['stress_repayment_ratio_percent'],
            'screening_status' => $preview['screening']['status'],
            'screening_advice' => $preview['screening']['advice'],
        ];
    }

    public function forProperty(?Property $property = null): static
    {
        return $this->state(fn () => [
            'property_id' => $property?->id ?? Property::factory(),
        ]);
    }
}
