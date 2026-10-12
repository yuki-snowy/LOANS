@extends('layouts.app')

@section('title', 'ローン試算')

@section('content')
    <div class="mb-8">
        <h1 class="font-serif text-3xl text-stone-900">ローン試算・審査判定</h1>
        <p class="mt-1 text-sm text-stone-500">元利均等返済と返済負担率・審査金利ストレステストで通過可能性を見積もります。</p>
    </div>

    <form method="POST" action="{{ route('simulations.store') }}" class="grid gap-8 lg:grid-cols-2">
        @csrf

        <div class="space-y-6">
            <section class="border border-stone-200 bg-white p-6">
                <h2 class="font-serif text-xl text-stone-900">借入条件</h2>
                <p class="mt-1 text-xs text-stone-500">物件を選ぶと価格を借入額の初期値に使えます。</p>

                <div class="mt-5 grid gap-4">
                    <div>
                        <label for="property_id" class="mb-1 block text-sm font-medium text-stone-700">対象物件</label>
                        <select name="property_id" id="property_id" class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
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
                            <label for="borrowing_amount" class="mb-1 block text-sm font-medium text-stone-700">借入希望額（万円） <span class="text-red-600">*</span></label>
                            <input type="number" name="borrowing_amount" id="borrowing_amount" min="1" max="999999" required
                                   value="{{ old('borrowing_amount', $selectedProperty?->price ?? 3000) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                        <div>
                            <label for="interest_rate" class="mb-1 block text-sm font-medium text-stone-700">適用金利（%） <span class="text-red-600">*</span></label>
                            <input type="number" name="interest_rate" id="interest_rate" step="0.001" min="0" max="20" required
                                   value="{{ old('interest_rate', '0.500') }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="repayment_years" class="mb-1 block text-sm font-medium text-stone-700">返済期間（年） <span class="text-red-600">*</span></label>
                            <input type="number" name="repayment_years" id="repayment_years" min="1" max="50" required
                                   value="{{ old('repayment_years', 35) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                        <div>
                            <label for="bonus_payment_amount" class="mb-1 block text-sm font-medium text-stone-700">ボーナス時加算額（万円）</label>
                            <input type="number" name="bonus_payment_amount" id="bonus_payment_amount" min="0" max="99999"
                                   value="{{ old('bonus_payment_amount', 0) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                    </div>
                </div>
            </section>

            <section class="border border-stone-200 bg-white p-6">
                <h2 class="font-serif text-xl text-stone-900">月額固定費</h2>
                <p class="mt-1 text-xs text-stone-500">ローン返済に加え、口座から引き落される管理費等です。</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="management_fee" class="mb-1 block text-sm font-medium text-stone-700">管理費（円）</label>
                        <input type="number" name="management_fee" id="management_fee" min="0" max="999999"
                               value="{{ old('management_fee', 12000) }}"
                               class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                    </div>
                    <div>
                        <label for="repair_reserve_fee" class="mb-1 block text-sm font-medium text-stone-700">修繕積立金（円）</label>
                        <input type="number" name="repair_reserve_fee" id="repair_reserve_fee" min="0" max="999999"
                               value="{{ old('repair_reserve_fee', 8000) }}"
                               class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                    </div>
                    <div>
                        <label for="parking_fee" class="mb-1 block text-sm font-medium text-stone-700">駐車場代（円）</label>
                        <input type="number" name="parking_fee" id="parking_fee" min="0" max="999999"
                               value="{{ old('parking_fee', 0) }}"
                               class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="border border-stone-200 bg-white p-6">
                <h2 class="font-serif text-xl text-stone-900">審査判定入力</h2>
                <p class="mt-1 text-xs text-stone-500">返済負担率と審査金利（ストレステスト）で通過可能性を判定します。</p>

                <div class="mt-5 grid gap-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="annual_income" class="mb-1 block text-sm font-medium text-stone-700">年収（万円） <span class="text-red-600">*</span></label>
                            <input type="number" name="annual_income" id="annual_income" min="1" max="999999" required
                                   value="{{ old('annual_income', 800) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                        <div>
                            <label for="years_of_service" class="mb-1 block text-sm font-medium text-stone-700">勤続年数 <span class="text-red-600">*</span></label>
                            <input type="number" name="years_of_service" id="years_of_service" min="0" max="60" required
                                   value="{{ old('years_of_service', 5) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                        </div>
                    </div>

                    <div>
                        <label for="employment_type" class="mb-1 block text-sm font-medium text-stone-700">雇用形態 <span class="text-red-600">*</span></label>
                        <select name="employment_type" id="employment_type" required
                                class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                            @foreach ($employmentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('employment_type', 'full_time') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="existing_annual_repayment" class="mb-1 block text-sm font-medium text-stone-700">既存年間返済額（万円）</label>
                            <input type="number" name="existing_annual_repayment" id="existing_annual_repayment" min="0" max="999999"
                                   value="{{ old('existing_annual_repayment', 0) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                            <p class="mt-1 text-xs text-stone-500">マイカー・リボ・その他借入の年間返済合計</p>
                        </div>
                        <div>
                            <label for="review_interest_rate" class="mb-1 block text-sm font-medium text-stone-700">審査金利（%）</label>
                            <input type="number" name="review_interest_rate" id="review_interest_rate" step="0.001" min="0" max="20"
                                   value="{{ old('review_interest_rate', number_format($defaultReviewRate, 3, '.', '')) }}"
                                   class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700">
                            <p class="mt-1 text-xs text-stone-500">一般的な審査金利は 3.0〜3.5%</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-teal-800 px-5 py-2.5 text-sm text-white transition hover:bg-teal-700">
                    計算して保存
                </button>
                <a href="{{ route('simulations.index') }}" class="border border-stone-300 px-5 py-2.5 text-sm text-stone-700 transition hover:bg-stone-50">
                    履歴へ戻る
                </a>
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
