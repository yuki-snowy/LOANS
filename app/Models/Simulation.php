<?php

namespace App\Models;

use App\Services\LoanCalculatorService;
use Database\Factories\SimulationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

#[Fillable([
    'property_id',
    'purchase_price',
    'down_payment',
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
    'monthly_payment_yen',
    'total_repayment_yen',
    'total_interest_yen',
    'actual_monthly_outflow_yen',
    'annual_loan_repayment_yen',
    'repayment_ratio_percent',
    'stress_repayment_ratio_percent',
    'screening_status',
    'screening_advice',
])]
class Simulation extends Model
{
    /** @use HasFactory<SimulationFactory> */
    use HasFactory;

    public const EMPLOYMENT_LABELS = [
        'full_time' => '正社員',
        'contract' => '契約社員',
        'self_employed' => '自営業',
        'other' => 'その他',
    ];

    public const STATUS_LABELS = [
        'safe' => '安全',
        'caution' => '注意',
        'danger' => '危険',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'purchase_price' => ['required', 'integer', 'min:1', 'max:999999'],
            'borrowing_amount' => ['required', 'integer', 'min:1', 'max:999999'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:20'],
            'repayment_years' => ['required', 'integer', 'min:1', 'max:50'],
            'bonus_payment_amount' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'management_fee' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'repair_reserve_fee' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'parking_fee' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'annual_income' => ['required', 'integer', 'min:1', 'max:999999'],
            'employment_type' => ['required', 'string', Rule::in(LoanCalculatorService::EMPLOYMENT_TYPES)],
            'years_of_service' => ['required', 'integer', 'min:0', 'max:60'],
            'existing_annual_repayment' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'review_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributeLabels(): array
    {
        return [
            'property_id' => '対象物件',
            'purchase_price' => '物件価格（万円）',
            'borrowing_amount' => '借入希望額（万円）',
            'interest_rate' => '適用金利（%）',
            'repayment_years' => '返済期間（年）',
            'bonus_payment_amount' => 'ボーナス時加算額（万円）',
            'management_fee' => '管理費（月額・円）',
            'repair_reserve_fee' => '修繕積立金（月額・円）',
            'parking_fee' => '駐車場代（月額・円）',
            'annual_income' => '年収（万円）',
            'employment_type' => '雇用形態',
            'years_of_service' => '勤続年数',
            'existing_annual_repayment' => '既存年間返済額（万円）',
            'review_interest_rate' => '審査金利（%）',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'integer',
            'down_payment' => 'integer',
            'borrowing_amount' => 'integer',
            'interest_rate' => 'decimal:3',
            'repayment_years' => 'integer',
            'bonus_payment_amount' => 'integer',
            'management_fee' => 'integer',
            'repair_reserve_fee' => 'integer',
            'parking_fee' => 'integer',
            'annual_income' => 'integer',
            'years_of_service' => 'integer',
            'existing_annual_repayment' => 'integer',
            'review_interest_rate' => 'decimal:3',
            'monthly_payment_yen' => 'integer',
            'total_repayment_yen' => 'integer',
            'total_interest_yen' => 'integer',
            'actual_monthly_outflow_yen' => 'integer',
            'annual_loan_repayment_yen' => 'integer',
            'repayment_ratio_percent' => 'decimal:2',
            'stress_repayment_ratio_percent' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function employmentLabel(): string
    {
        return self::EMPLOYMENT_LABELS[$this->employment_type] ?? $this->employment_type;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->screening_status] ?? $this->screening_status;
    }
}
