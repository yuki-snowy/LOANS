@extends('layouts.app')

@section('title', 'ローン試算')

@section('content')
    <div class="mb-5">
        <h1 class="section-title">ローン試算・審査判定</h1>
        <p class="mt-2 text-sm text-muted">元利均等返済と返済負担率・審査金利ストレステストで通過可能性を見積もります。</p>
    </div>

    <form method="POST" action="{{ route('simulations.store') }}" class="grid gap-5 lg:grid-cols-2">
        @csrf

        <div class="space-y-5">
            <section class="border border-line bg-white">
                <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">借入条件</h2>
                <div class="grid gap-4 p-4 sm:p-5">
                    <p class="text-xs text-muted">物件を選ぶと価格を借入額の初期値に使えます。</p>
                    <div>
                        <label for="property_id" class="mb-1 block text-sm font-bold">対象物件</label>
                        <select name="property_id" id="property_id" class="input-suumo">
                            <option value="">物件を指定しない</option>
                            @foreach ($properties as $property)
                                <option
                                    value="{{ $property->id }}"
                                    data-price="{{ $property->price }}"
                                    @selected((string) old('property_id', $selectedProperty?->id) === (string) $property->id)
                                >
                                    {{ $property->name }}（{{ number_format($property->price) }}万円）
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="borrowing_amount" class="mb-1 block text-sm font-bold">借入希望額（万円） <span class="text-price">*</span></label>
                            <input type="number" name="borrowing_amount" id="borrowing_amount" min="1" max="999999" required
                                   value="{{ old('borrowing_amount', $selectedProperty?->price ?? 3000) }}"
                                   class="input-suumo">
                        </div>
                        <div>
                            <label for="interest_rate" class="mb-1 block text-sm font-bold">適用金利（%） <span class="text-price">*</span></label>
                            <input type="number" name="interest_rate" id="interest_rate" step="0.001" min="0" max="20" required
                                   value="{{ old('interest_rate', '0.500') }}"
                                   class="input-suumo">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="repayment_years" class="mb-1 block text-sm font-bold">返済期間（年） <span class="text-price">*</span></label>
                            <input type="number" name="repayment_years" id="repayment_years" min="1" max="50" required
                                   value="{{ old('repayment_years', 35) }}"
                                   class="input-suumo">
                        </div>
                        <div>
                            <label for="bonus_payment_amount" class="mb-1 block text-sm font-bold">ボーナス時加算額（万円）</label>
                            <input type="number" name="bonus_payment_amount" id="bonus_payment_amount" min="0" max="99999"
                                   value="{{ old('bonus_payment_amount', 0) }}"
                                   class="input-suumo">
                        </div>
                    </div>
                </div>
            </section>

            <section class="border border-line bg-white">
                <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">月額固定費</h2>
                <div class="grid gap-4 p-4 sm:grid-cols-3 sm:p-5">
                    <div>
                        <label for="management_fee" class="mb-1 block text-sm font-bold">管理費（円）</label>
                        <input type="number" name="management_fee" id="management_fee" min="0" max="999999"
                               value="{{ old('management_fee', 12000) }}" class="input-suumo">
                    </div>
                    <div>
                        <label for="repair_reserve_fee" class="mb-1 block text-sm font-bold">修繕積立金（円）</label>
                        <input type="number" name="repair_reserve_fee" id="repair_reserve_fee" min="0" max="999999"
                               value="{{ old('repair_reserve_fee', 8000) }}" class="input-suumo">
                    </div>
                    <div>
                        <label for="parking_fee" class="mb-1 block text-sm font-bold">駐車場代（円）</label>
                        <input type="number" name="parking_fee" id="parking_fee" min="0" max="999999"
                               value="{{ old('parking_fee', 0) }}" class="input-suumo">
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-5">
            <section class="border border-line bg-white">
                <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">審査判定入力</h2>
                <div class="grid gap-4 p-4 sm:p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="annual_income" class="mb-1 block text-sm font-bold">年収（万円） <span class="text-price">*</span></label>
                            <input type="number" name="annual_income" id="annual_income" min="1" max="999999" required
                                   value="{{ old('annual_income', 800) }}" class="input-suumo">
                        </div>
                        <div>
                            <label for="years_of_service" class="mb-1 block text-sm font-bold">勤続年数 <span class="text-price">*</span></label>
                            <input type="number" name="years_of_service" id="years_of_service" min="0" max="60" required
                                   value="{{ old('years_of_service', 5) }}" class="input-suumo">
                        </div>
                    </div>

                    <div>
                        <label for="employment_type" class="mb-1 block text-sm font-bold">雇用形態 <span class="text-price">*</span></label>
                        <select name="employment_type" id="employment_type" required class="input-suumo">
                            @foreach ($employmentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('employment_type', 'full_time') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="existing_annual_repayment" class="mb-1 block text-sm font-bold">既存年間返済額（万円）</label>
                            <input type="number" name="existing_annual_repayment" id="existing_annual_repayment" min="0" max="999999"
                                   value="{{ old('existing_annual_repayment', 0) }}" class="input-suumo">
                            <p class="mt-1 text-xs text-muted">マイカー・リボ・その他借入の年間返済合計</p>
                        </div>
                        <div>
                            <label for="review_interest_rate" class="mb-1 block text-sm font-bold">審査金利（%）</label>
                            <input type="number" name="review_interest_rate" id="review_interest_rate" step="0.001" min="0" max="20"
                                   value="{{ old('review_interest_rate', number_format($defaultReviewRate, 3, '.', '')) }}"
                                   class="input-suumo">
                            <p class="mt-1 text-xs text-muted">一般的な審査金利は 3.0〜3.5%</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-suumo px-6 py-2.5">計算して保存</button>
                <a href="{{ route('simulations.index') }}" class="btn-suumo-outline px-6 py-2.5">履歴へ戻る</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('property_id')?.addEventListener('change', (event) => {
        const option = event.target.selectedOptions[0];
        const price = option?.dataset?.price;
        const amount = document.getElementById('borrowing_amount');
        if (price && amount && !amount.dataset.touched) {
            amount.value = price;
        }
    });
    document.getElementById('borrowing_amount')?.addEventListener('input', (event) => {
        event.target.dataset.touched = '1';
    });
</script>
@endpush
