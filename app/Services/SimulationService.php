<?php

namespace App\Services;

use App\Models\Simulation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SimulationService
{
    public function __construct(
        private readonly LoanCalculatorService $loanCalculator
    ) {}

    /**
     * @return LengthAwarePaginator<int, Simulation>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Simulation::query()
            ->with('property')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 入力から計算しシミュレーションを保存する。
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): Simulation
    {
        $payload = $this->normalizeInput($input);
        $computed = $this->compute($payload);

        return DB::transaction(fn () => Simulation::query()->create([
            ...$payload,
            ...$computed,
        ]));
    }

    /**
     * 保存せず計算結果だけ返す（プレビュー用）。
     *
     * @param  array<string, mixed>  $input
     * @return array{input: array<string, mixed>, repayment: array<string, int>, screening: array<string, mixed>}
     */
    public function preview(array $input): array
    {
        $payload = $this->normalizeInput($input);
        $repayment = $this->loanCalculator->simulateRepayment(
            borrowingAmountMan: $payload['borrowing_amount'],
            interestRatePercent: (float) $payload['interest_rate'],
            repaymentYears: $payload['repayment_years'],
            bonusPaymentAmountMan: $payload['bonus_payment_amount'],
            managementFeeYen: $payload['management_fee'],
            repairReserveFeeYen: $payload['repair_reserve_fee'],
            parkingFeeYen: $payload['parking_fee'],
        );

        $screening = $this->loanCalculator->evaluateScreening(
            annualIncomeMan: $payload['annual_income'],
            employmentType: $payload['employment_type'],
            yearsOfService: $payload['years_of_service'],
            existingAnnualRepaymentMan: $payload['existing_annual_repayment'],
            annualLoanRepaymentYen: $repayment['annual_loan_repayment_yen'],
            borrowingAmountMan: $payload['borrowing_amount'],
            repaymentYears: $payload['repayment_years'],
            reviewInterestRatePercent: (float) $payload['review_interest_rate'],
        );

        return [
            'input' => $payload,
            'repayment' => $repayment,
            'screening' => $screening,
        ];
    }

    public function delete(Simulation $simulation): void
    {
        DB::transaction(fn () => $simulation->delete());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function compute(array $payload): array
    {
        $preview = $this->preview($payload);

        return [
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

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeInput(array $input): array
    {
        return [
            'property_id' => $input['property_id'] ?? null,
            'borrowing_amount' => (int) $input['borrowing_amount'],
            'interest_rate' => (float) $input['interest_rate'],
            'repayment_years' => (int) $input['repayment_years'],
            'bonus_payment_amount' => (int) ($input['bonus_payment_amount'] ?? 0),
            'management_fee' => (int) ($input['management_fee'] ?? 0),
            'repair_reserve_fee' => (int) ($input['repair_reserve_fee'] ?? 0),
            'parking_fee' => (int) ($input['parking_fee'] ?? 0),
            'annual_income' => (int) $input['annual_income'],
            'employment_type' => (string) $input['employment_type'],
            'years_of_service' => (int) $input['years_of_service'],
            'existing_annual_repayment' => (int) ($input['existing_annual_repayment'] ?? 0),
            'review_interest_rate' => (float) ($input['review_interest_rate'] ?? LoanCalculatorService::DEFAULT_REVIEW_RATE_PERCENT),
        ];
    }
}
