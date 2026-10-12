<?php

namespace Tests\Unit;

use App\Services\LoanCalculatorService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoanCalculatorServiceTest extends TestCase
{
    private LoanCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LoanCalculatorService;
    }

    #[Test]
    public function calculates_equal_payment_monthly_repayment_in_yen(): void
    {
        // 3,000万円 / 0.5% / 35年（元利均等）
        $result = $this->service->simulateRepayment(
            borrowingAmountMan: 3000,
            interestRatePercent: 0.5,
            repaymentYears: 35,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        $this->assertSame(77_876, $result['monthly_payment_yen']);
        $this->assertSame(32_707_920, $result['total_repayment_yen']);
        $this->assertSame(2_707_920, $result['total_interest_yen']);
        $this->assertSame(77_876, $result['actual_monthly_outflow_yen']);
        $this->assertSame(934_512, $result['annual_loan_repayment_yen']);
    }

    #[Test]
    public function zero_interest_divides_principal_evenly(): void
    {
        // 1,200万円 / 0% / 10年 = 月10万円ちょうど
        $result = $this->service->simulateRepayment(
            borrowingAmountMan: 1200,
            interestRatePercent: 0.0,
            repaymentYears: 10,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        $this->assertSame(100_000, $result['monthly_payment_yen']);
        $this->assertSame(12_000_000, $result['total_repayment_yen']);
        $this->assertSame(0, $result['total_interest_yen']);
    }

    #[Test]
    public function includes_fixed_monthly_costs_in_actual_outflow(): void
    {
        $result = $this->service->simulateRepayment(
            borrowingAmountMan: 1200,
            interestRatePercent: 0.0,
            repaymentYears: 10,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 15_000,
            repairReserveFeeYen: 8_000,
            parkingFeeYen: 12_000,
        );

        // 100,000 + 15,000 + 8,000 + 12,000
        $this->assertSame(135_000, $result['actual_monthly_outflow_yen']);
        $this->assertSame(35_000, $result['monthly_fixed_costs_yen']);
    }

    #[Test]
    public function bonus_payment_increases_annual_loan_repayment(): void
    {
        $result = $this->service->simulateRepayment(
            borrowingAmountMan: 1200,
            interestRatePercent: 0.0,
            repaymentYears: 10,
            bonusPaymentAmountMan: 20, // 20万円 × 年2回
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        // 月10万 × 12 + 20万×2 = 120万 + 40万 = 160万円
        $this->assertSame(1_600_000, $result['annual_loan_repayment_yen']);
        $this->assertSame(200_000, $result['bonus_payment_yen']);
    }

    #[Test]
    public function rejects_non_positive_borrowing_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->simulateRepayment(
            borrowingAmountMan: 0,
            interestRatePercent: 0.5,
            repaymentYears: 35,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );
    }

    #[Test]
    public function rejects_non_positive_repayment_years(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->simulateRepayment(
            borrowingAmountMan: 3000,
            interestRatePercent: 0.5,
            repaymentYears: 0,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );
    }

    #[Test]
    public function rejects_negative_interest_rate(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->simulateRepayment(
            borrowingAmountMan: 3000,
            interestRatePercent: -0.1,
            repaymentYears: 35,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );
    }

    #[Test]
    public function screening_marks_safe_when_ratio_within_25_percent(): void
    {
        // 年収800万円、既存借入なし、ローン年間約93.45万円 → 約11.7%
        $repayment = $this->service->simulateRepayment(
            borrowingAmountMan: 3000,
            interestRatePercent: 0.5,
            repaymentYears: 35,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: $repayment['annual_loan_repayment_yen'],
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertSame('safe', $screening['status']);
        $this->assertLessThanOrEqual(25.0, $screening['repayment_ratio_percent']);
        $this->assertNotEmpty($screening['advice']);
    }

    #[Test]
    public function screening_marks_caution_between_25_and_30_percent(): void
    {
        // 月返済10万円 → 年120万円、年収450万円 → 26.67%
        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 450,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 1_200_000,
            borrowingAmountMan: 1200,
            repaymentYears: 10,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertSame('caution', $screening['status']);
        $this->assertEqualsWithDelta(26.67, $screening['repayment_ratio_percent'], 0.01);
    }

    #[Test]
    public function screening_marks_danger_over_30_percent(): void
    {
        // 年返済200万円 / 年収500万円 = 40%
        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 500,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 2_000_000,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertSame('danger', $screening['status']);
        $this->assertEqualsWithDelta(40.0, $screening['repayment_ratio_percent'], 0.01);
    }

    #[Test]
    public function screening_includes_existing_debt_in_ratio(): void
    {
        // ローン年100万 + 既存年50万 = 150万 / 年収500万 = 30%（30%超で危険、ちょうど30%は注意）
        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 500,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 50,
            annualLoanRepaymentYen: 1_000_000,
            borrowingAmountMan: 2000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertEqualsWithDelta(30.0, $screening['repayment_ratio_percent'], 0.01);
        $this->assertSame('caution', $screening['status']);

        // 既存をさらに増やして30%超にする
        $over = $this->service->evaluateScreening(
            annualIncomeMan: 500,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 60,
            annualLoanRepaymentYen: 1_000_000,
            borrowingAmountMan: 2000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertGreaterThan(30.0, $over['repayment_ratio_percent']);
        $this->assertSame('danger', $over['status']);
    }

    #[Test]
    public function stress_test_uses_review_interest_rate(): void
    {
        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 934_512,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertArrayHasKey('stress_repayment_ratio_percent', $screening);
        $this->assertArrayHasKey('stress_annual_loan_repayment_yen', $screening);
        $this->assertGreaterThan(
            $screening['repayment_ratio_percent'],
            $screening['stress_repayment_ratio_percent']
        );

        // 審査金利3%での月返済が適用金利より高いこと
        $stress = $this->service->simulateRepayment(
            borrowingAmountMan: 3000,
            interestRatePercent: 3.0,
            repaymentYears: 35,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );
        $this->assertSame($stress['annual_loan_repayment_yen'], $screening['stress_annual_loan_repayment_yen']);
    }

    #[Test]
    public function short_tenure_or_non_full_time_worsens_status(): void
    {
        // 比率は安全圏でも、勤続1年未満や自営業は注意へ引き上げ
        $base = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 900_000,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );
        $this->assertSame('safe', $base['status']);

        $shortTenure = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: 'full_time',
            yearsOfService: 0,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 900_000,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );
        $this->assertSame('caution', $shortTenure['status']);

        $selfEmployed = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: 'self_employed',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 900_000,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );
        $this->assertSame('caution', $selfEmployed['status']);
    }

    #[Test]
    public function rejects_zero_annual_income_for_screening(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->evaluateScreening(
            annualIncomeMan: 0,
            employmentType: 'full_time',
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 1_000_000,
            borrowingAmountMan: 3000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );
    }

    #[Test]
    #[DataProvider('employmentTypeProvider')]
    public function accepts_known_employment_types(string $employmentType): void
    {
        $screening = $this->service->evaluateScreening(
            annualIncomeMan: 800,
            employmentType: $employmentType,
            yearsOfService: 5,
            existingAnnualRepaymentMan: 0,
            annualLoanRepaymentYen: 500_000,
            borrowingAmountMan: 2000,
            repaymentYears: 35,
            reviewInterestRatePercent: 3.0,
        );

        $this->assertContains($screening['status'], ['safe', 'caution', 'danger']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function employmentTypeProvider(): array
    {
        return [
            'full_time' => ['full_time'],
            'contract' => ['contract'],
            'self_employed' => ['self_employed'],
            'other' => ['other'],
        ];
    }

    #[Test]
    public function monthly_payment_uses_half_up_rounding_to_yen(): void
    {
        // 端数が出る条件でも整数円になること
        $result = $this->service->simulateRepayment(
            borrowingAmountMan: 3333,
            interestRatePercent: 0.625,
            repaymentYears: 33,
            bonusPaymentAmountMan: 0,
            managementFeeYen: 0,
            repairReserveFeeYen: 0,
            parkingFeeYen: 0,
        );

        $this->assertIsInt($result['monthly_payment_yen']);
        $this->assertIsInt($result['total_repayment_yen']);
        $this->assertIsInt($result['total_interest_yen']);
        $this->assertSame(
            $result['monthly_payment_yen'] * 33 * 12,
            $result['total_repayment_yen']
        );
    }
}
