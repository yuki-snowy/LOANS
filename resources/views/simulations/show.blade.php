@extends('layouts.app')

@section('title', 'シミュレーション結果')

@section('content')
    @php
        $statusStyles = [
            'safe' => 'border-emerald-300 bg-emerald-50',
            'caution' => 'border-amber-300 bg-amber-50',
            'danger' => 'border-red-300 bg-red-50',
        ];
        $statusText = [
            'safe' => 'text-emerald-900',
            'caution' => 'text-amber-950',
            'danger' => 'text-red-900',
        ];
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-stone-500">シミュレーション結果</p>
            <h1 class="mt-1 font-serif text-3xl text-stone-900">
                {{ $simulation->property?->name ?? '物件未指定の試算' }}
            </h1>
            <p class="mt-2 text-sm text-stone-600">
                借入 {{ number_format($simulation->borrowing_amount) }}万円 /
                {{ number_format((float) $simulation->interest_rate, 3) }}% /
                {{ $simulation->repayment_years }}年
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('simulations.create', ['property_id' => $simulation->property_id]) }}" class="bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">条件を変えて再試算</a>
            <form method="POST" action="{{ route('simulations.destroy', $simulation) }}" onsubmit="return confirm('このシミュレーションを削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="border border-red-300 px-4 py-2 text-sm text-red-700 transition hover:bg-red-50">削除</button>
            </form>
            <a href="{{ route('simulations.index') }}" class="border border-stone-300 px-4 py-2 text-sm text-stone-700 transition hover:bg-stone-50">履歴へ</a>
        </div>
    </div>

    {{-- 審査シグナル --}}
    <section class="mb-6 border p-6 {{ $statusStyles[$simulation->screening_status] ?? 'border-stone-200 bg-white' }}">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-serif text-2xl {{ $statusText[$simulation->screening_status] ?? 'text-stone-900' }}">
                審査シグナル：{{ $simulation->statusLabel() }}
            </h2>
            @include('simulations._status_badge', ['status' => $simulation->screening_status])
        </div>
        <p class="mt-3 text-sm leading-relaxed {{ $statusText[$simulation->screening_status] ?? 'text-stone-700' }}">
            {{ $simulation->screening_advice }}
        </p>
        <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-xs opacity-70">返済負担率（適用金利）</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format((float) $simulation->repayment_ratio_percent, 2) }}%</dd>
            </div>
            <div>
                <dt class="text-xs opacity-70">返済負担率（審査金利）</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format((float) $simulation->stress_repayment_ratio_percent, 2) }}%</dd>
            </div>
            <div>
                <dt class="text-xs opacity-70">年収</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($simulation->annual_income) }}万円</dd>
            </div>
            <div>
                <dt class="text-xs opacity-70">雇用 / 勤続</dt>
                <dd class="mt-1 text-xl font-semibold">{{ $simulation->employmentLabel() }} / {{ $simulation->years_of_service }}年</dd>
            </div>
        </dl>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="border border-stone-200 bg-white p-6">
            <h2 class="font-serif text-xl text-stone-900">詳細返済シミュレーション</h2>
            <p class="mt-1 text-xs text-stone-500">元利均等返済・円単位四捨五入</p>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-stone-500">月々のローン返済額</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums text-stone-900">{{ number_format($simulation->monthly_payment_yen) }}<span class="ml-1 text-sm font-normal text-stone-500">円</span></dd>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">実際の毎月引き落とし総額</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums text-teal-800">{{ number_format($simulation->actual_monthly_outflow_yen) }}<span class="ml-1 text-sm font-normal text-stone-500">円</span></dd>
                    <p class="mt-1 text-xs text-stone-500">ローン + 管理費 + 修繕積立金 + 駐車場</p>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">総返済額</dt>
                    <dd class="mt-1 text-lg font-semibold tabular-nums">{{ number_format($simulation->total_repayment_yen) }}円</dd>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">総利息</dt>
                    <dd class="mt-1 text-lg font-semibold tabular-nums">{{ number_format($simulation->total_interest_yen) }}円</dd>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">年間ローン返済額</dt>
                    <dd class="mt-1 text-lg font-semibold tabular-nums">{{ number_format($simulation->annual_loan_repayment_yen) }}円</dd>
                    <p class="mt-1 text-xs text-stone-500">ボーナス加算（年2回）込み</p>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">ボーナス時加算</dt>
                    <dd class="mt-1 text-lg font-semibold tabular-nums">{{ number_format($simulation->bonus_payment_amount) }}万円 / 回</dd>
                </div>
            </dl>
        </section>

        <section class="border border-stone-200 bg-white p-6">
            <h2 class="font-serif text-xl text-stone-900">入力条件</h2>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-stone-100 pb-2">
                    <dt class="text-stone-500">管理費</dt>
                    <dd class="tabular-nums">{{ number_format($simulation->management_fee) }}円/月</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-stone-100 pb-2">
                    <dt class="text-stone-500">修繕積立金</dt>
                    <dd class="tabular-nums">{{ number_format($simulation->repair_reserve_fee) }}円/月</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-stone-100 pb-2">
                    <dt class="text-stone-500">駐車場代</dt>
                    <dd class="tabular-nums">{{ number_format($simulation->parking_fee) }}円/月</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-stone-100 pb-2">
                    <dt class="text-stone-500">既存年間返済額</dt>
                    <dd class="tabular-nums">{{ number_format($simulation->existing_annual_repayment) }}万円</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-stone-100 pb-2">
                    <dt class="text-stone-500">審査金利</dt>
                    <dd class="tabular-nums">{{ number_format((float) $simulation->review_interest_rate, 3) }}%</dd>
                </div>
                @if ($simulation->property)
                    <div class="flex justify-between gap-4 pt-1">
                        <dt class="text-stone-500">物件詳細</dt>
                        <dd>
                            <a href="{{ route('properties.show', $simulation->property) }}" class="text-teal-800 hover:underline">
                                {{ $simulation->property->name }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 border border-stone-100 bg-stone-50 p-4 text-xs leading-relaxed text-stone-600">
                <p class="font-medium text-stone-800">判定の目安</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    <li>返済負担率 25%以内 → 安全（都市銀行の厳しめ目安）</li>
                    <li>25%超〜30%以内 → 注意（フラット35目安はクリア）</li>
                    <li>30%超 → 危険（審査が厳しくなりやすい）</li>
                    <li>審査金利でのストレステストと雇用・勤続も加味</li>
                </ul>
            </div>
        </section>
    </div>
@endsection
