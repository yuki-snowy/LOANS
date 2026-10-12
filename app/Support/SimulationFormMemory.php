<?php

namespace App\Support;

use App\Models\Simulation;

final class SimulationFormMemory
{
    public const SESSION_KEY = 'simulation.form_memory';

    /** @var list<string> */
    public const FIELDS = [
        'property_id',
        'purchase_price',
        'borrowing_amount',
        'interest_rate',
        'repayment_years',
        'bonus_payment_amount',
        'management_fee',
        'repair_reserve_fee',
        'parking_fee',
        'annual_income',
        'employment_type',
        'years_of_service',
        'existing_annual_repayment',
        'review_interest_rate',
    ];

    /**
     * @param  array<string, mixed>  $input
     */
    public static function remember(array $input): void
    {
        $payload = [];
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $input)) {
                $payload[$field] = $input[$field];
            }
        }

        session([self::SESSION_KEY => $payload]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function recall(): array
    {
        $stored = session(self::SESSION_KEY, []);

        return is_array($stored) ? $stored : [];
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function has(): bool
    {
        return self::recall() !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromSimulation(Simulation $simulation): array
    {
        $payload = [];
        foreach (self::FIELDS as $field) {
            $payload[$field] = $simulation->{$field};
        }

        return $payload;
    }
}
