<?php

namespace App\Services;

use InvalidArgumentException;

class LoanCalculatorService
{
    public const STATUS_SAFE = 'safe';

    public const STATUS_CAUTION = 'caution';

    public const STATUS_DANGER = 'danger';

    public const DEFAULT_REVIEW_RATE_PERCENT = 3.0;

    public const EMPLOYMENT_TYPES = [
        'full_time',
        'contract',
        'self_employed',
        'other',
    ];

    /**
     * 元利均等返済シミュレーション（円単位・四捨五入）。
     *
     * @return array{
     *     principal_yen: int,
     *     monthly_payment_yen: int,
     *     total_repayment_yen: int,
     *     total_interest_yen: int,
     *     bonus_payment_yen: int,
     *     monthly_fixed_costs_yen: int,
     *     actual_monthly_outflow_yen: int,
     *     annual_loan_repayment_yen: int,
     *     repayment_months: int
     * }
     */
    public function simulateRepayment(
        int $borrowingAmountMan,
        float $interestRatePercent,
        int $repaymentYears,
        int $bonusPaymentAmountMan = 0,
        int $managementFeeYen = 0,
        int $repairReserveFeeYen = 0,
        int $parkingFeeYen = 0,
    ): array {
        $this->assertRepaymentInputs(
            $borrowingAmountMan,
            $interestRatePercent,
            $repaymentYears,
            $bonusPaymentAmountMan,
            $managementFeeYen,
            $repairReserveFeeYen,
            $parkingFeeYen,
        );

        $principalYen = $borrowingAmountMan * 10_000;
        $months = $repaymentYears * 12;
        $monthlyPaymentYen = $this->calculateMonthlyPaymentYen(
            $principalYen,
            $interestRatePercent,
            $months
        );

        $totalRepaymentYen = $monthlyPaymentYen * $months;
        $totalInterestYen = $totalRepaymentYen - $principalYen;
        $bonusPaymentYen = $bonusPaymentAmountMan * 10_000;
        $monthlyFixedCostsYen = $managementFeeYen + $repairReserveFeeYen + $parkingFeeYen;
        $actualMonthlyOutflowYen = $monthlyPaymentYen + $monthlyFixedCostsYen;
        $annualLoanRepaymentYen = ($monthlyPaymentYen * 12) + ($bonusPaymentYen * 2);

        return [
            'principal_yen' => $principalYen,
            'monthly_payment_yen' => $monthlyPaymentYen,
            'total_repayment_yen' => $totalRepaymentYen,
            'total_interest_yen' => $totalInterestYen,
            'bonus_payment_yen' => $bonusPaymentYen,
            'monthly_fixed_costs_yen' => $monthlyFixedCostsYen,
            'actual_monthly_outflow_yen' => $actualMonthlyOutflowYen,
            'annual_loan_repayment_yen' => $annualLoanRepaymentYen,
            'repayment_months' => $months,
        ];
    }

    /**
     * ローン審査通過可能性の判定。
     *
     * @return array{
     *     status: string,
     *     repayment_ratio_percent: float,
     *     stress_repayment_ratio_percent: float,
     *     stress_annual_loan_repayment_yen: int,
     *     annual_income_yen: int,
     *     total_annual_debt_yen: int,
     *     advice: string
     * }
     */
    public function evaluateScreening(
        int $annualIncomeMan,
        string $employmentType,
        int $yearsOfService,
        int $existingAnnualRepaymentMan,
        int $annualLoanRepaymentYen,
        int $borrowingAmountMan,
        int $repaymentYears,
        float $reviewInterestRatePercent = self::DEFAULT_REVIEW_RATE_PERCENT,
    ): array {
        if ($annualIncomeMan <= 0) {
            throw new InvalidArgumentException('年収は1万円以上で指定してください。');
        }

        if ($yearsOfService < 0) {
            throw new InvalidArgumentException('勤続年数は0以上で指定してください。');
        }

        if ($existingAnnualRepaymentMan < 0) {
            throw new InvalidArgumentException('既存年間返済額は0以上で指定してください。');
        }

        if ($annualLoanRepaymentYen < 0) {
            throw new InvalidArgumentException('年間ローン返済額は0以上で指定してください。');
        }

        if (! in_array($employmentType, self::EMPLOYMENT_TYPES, true)) {
            throw new InvalidArgumentException('雇用形態が不正です。');
        }

        if ($reviewInterestRatePercent < 0) {
            throw new InvalidArgumentException('審査金利は0以上で指定してください。');
        }

        $annualIncomeYen = $annualIncomeMan * 10_000;
        $existingAnnualYen = $existingAnnualRepaymentMan * 10_000;
        $totalAnnualDebtYen = $annualLoanRepaymentYen + $existingAnnualYen;
        $repaymentRatio = $this->calculateRepaymentRatioPercent($totalAnnualDebtYen, $annualIncomeYen);

        $stress = $this->simulateRepayment(
            borrowingAmountMan: $borrowingAmountMan,
            interestRatePercent: $reviewInterestRatePercent,
            repaymentYears: $repaymentYears,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        $stressAnnualDebtYen = $stress['annual_loan_repayment_yen'] + $existingAnnualYen;
        $stressRatio = $this->calculateRepaymentRatioPercent($stressAnnualDebtYen, $annualIncomeYen);

        $status = $this->resolveStatus($repaymentRatio, $stressRatio, $employmentType, $yearsOfService);
        $advice = $this->buildAdvice($status, $repaymentRatio, $stressRatio, $employmentType, $yearsOfService);

        return [
            'status' => $status,
            'repayment_ratio_percent' => $repaymentRatio,
            'stress_repayment_ratio_percent' => $stressRatio,
            'stress_annual_loan_repayment_yen' => $stress['annual_loan_repayment_yen'],
            'annual_income_yen' => $annualIncomeYen,
            'total_annual_debt_yen' => $totalAnnualDebtYen,
            'advice' => $advice,
        ];
    }

    /**
     * 円単位の月々返済額（四捨五入）。
     */
    public function calculateMonthlyPaymentYen(int $principalYen, float $annualRatePercent, int $months): int
    {
        if ($principalYen <= 0 || $months <= 0) {
            throw new InvalidArgumentException('元金と返済回数は正の値である必要があります。');
        }

        if ($annualRatePercent < 0) {
            throw new InvalidArgumentException('金利は0以上で指定してください。');
        }

        if ($annualRatePercent == 0.0) {
            return (int) round($principalYen / $months);
        }

        $monthlyRate = $annualRatePercent / 100 / 12;
        $factor = (1 + $monthlyRate) ** $months;
        $payment = $principalYen * $monthlyRate * $factor / ($factor - 1);

        return (int) round($payment);
    }

    private function calculateRepaymentRatioPercent(int $annualDebtYen, int $annualIncomeYen): float
    {
        return round(($annualDebtYen / $annualIncomeYen) * 100, 2);
    }

    private function resolveStatus(
        float $ratio,
        float $stressRatio,
        string $employmentType,
        int $yearsOfService,
    ): string {
        $status = match (true) {
            $ratio > 30 => self::STATUS_DANGER,
            $ratio > 25 => self::STATUS_CAUTION,
            default => self::STATUS_SAFE,
        };

        // 審査金利での負担率が30%超なら1段階悪化
        if ($stressRatio > 30 && $status === self::STATUS_SAFE) {
            $status = self::STATUS_CAUTION;
        } elseif ($stressRatio > 35 && $status === self::STATUS_CAUTION) {
            $status = self::STATUS_DANGER;
        }

        // 勤続・雇用形態による補正
        if ($status === self::STATUS_SAFE) {
            if ($yearsOfService < 1 || in_array($employmentType, ['contract', 'self_employed', 'other'], true)) {
                $status = self::STATUS_CAUTION;
            }
        }

        return $status;
    }

    private function buildAdvice(
        string $status,
        float $ratio,
        float $stressRatio,
        string $employmentType,
        int $yearsOfService,
    ): string {
        $parts = [];

        $parts[] = sprintf('適用金利ベースの返済負担率は %.2f%% です。', $ratio);
        $parts[] = sprintf('審査金利ベースでは %.2f%% です。', $stressRatio);

        if ($ratio <= 25) {
            $parts[] = '都市銀行の厳しめの目安（25%以内）も満たしています。';
        } elseif ($ratio <= 30) {
            $parts[] = 'フラット35の目安（30%以内）は満たしていますが、都市銀行の厳しめの審査（25%以内）では注意が必要です。';
        } else {
            $parts[] = '返済負担率が30%を超えており、多くの金融機関で審査が厳しくなる水準です。借入額や期間の見直しを検討してください。';
        }

        if ($stressRatio > 30) {
            $parts[] = '金利上昇（審査金利）を想定すると負担が重くなるため、余裕を持った計画が望ましいです。';
        }

        if ($yearsOfService < 1) {
            $parts[] = '勤続年数が短いため、勤続実績の蓄積や保証料条件の確認が必要です。';
        }

        if ($employmentType === 'self_employed') {
            $parts[] = '自営業の場合は直近2〜3期の所得安定性が重視されます。';
        } elseif ($employmentType === 'contract') {
            $parts[] = '契約社員の場合は契約更新実績や勤続安定性が審査に影響します。';
        }

        return match ($status) {
            self::STATUS_SAFE => '【安全】'.implode(' ', $parts),
            self::STATUS_CAUTION => '【注意】'.implode(' ', $parts),
            default => '【危険】'.implode(' ', $parts),
        };
    }

    private function assertRepaymentInputs(
        int $borrowingAmountMan,
        float $interestRatePercent,
        int $repaymentYears,
        int $bonusPaymentAmountMan,
        int $managementFeeYen,
        int $repairReserveFeeYen,
        int $parkingFeeYen,
    ): void {
        if ($borrowingAmountMan <= 0) {
            throw new InvalidArgumentException('借入希望額は1万円以上で指定してください。');
        }

        if ($interestRatePercent < 0) {
            throw new InvalidArgumentException('金利は0以上で指定してください。');
        }

        if ($repaymentYears <= 0) {
            throw new InvalidArgumentException('返済期間は1年以上で指定してください。');
        }

        if ($bonusPaymentAmountMan < 0) {
            throw new InvalidArgumentException('ボーナス加算額は0以上で指定してください。');
        }

        if ($managementFeeYen < 0 || $repairReserveFeeYen < 0 || $parkingFeeYen < 0) {
            throw new InvalidArgumentException('月額固定費は0以上で指定してください。');
        }
    }
}
