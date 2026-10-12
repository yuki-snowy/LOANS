<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->nullOnDelete();

            // ローン入力
            $table->unsignedInteger('borrowing_amount')->comment('借入希望額（万円）');
            $table->decimal('interest_rate', 5, 3)->comment('適用金利（%）');
            $table->unsignedTinyInteger('repayment_years')->comment('返済期間（年）');
            $table->unsignedInteger('bonus_payment_amount')->default(0)->comment('ボーナス時加算額（万円）');
            $table->unsignedInteger('management_fee')->default(0)->comment('管理費（月額・円）');
            $table->unsignedInteger('repair_reserve_fee')->default(0)->comment('修繕積立金（月額・円）');
            $table->unsignedInteger('parking_fee')->default(0)->comment('駐車場代（月額・円）');

            // 審査入力
            $table->unsignedInteger('annual_income')->comment('年収（万円）');
            $table->string('employment_type', 32)->comment('雇用形態');
            $table->unsignedTinyInteger('years_of_service')->default(0)->comment('勤続年数');
            $table->unsignedInteger('existing_annual_repayment')->default(0)->comment('既存年間返済額（万円）');
            $table->decimal('review_interest_rate', 5, 3)->default(3.000)->comment('審査金利（%）');

            // 計算結果スナップショット
            $table->unsignedBigInteger('monthly_payment_yen');
            $table->unsignedBigInteger('total_repayment_yen');
            $table->unsignedBigInteger('total_interest_yen');
            $table->unsignedBigInteger('actual_monthly_outflow_yen');
            $table->unsignedBigInteger('annual_loan_repayment_yen');
            $table->decimal('repayment_ratio_percent', 6, 2);
            $table->decimal('stress_repayment_ratio_percent', 6, 2);
            $table->string('screening_status', 16);
            $table->text('screening_advice');

            $table->timestamps();

            $table->index('property_id');
            $table->index('screening_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
