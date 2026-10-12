@extends('layouts.app')

@section('title', 'シミュレーション結果')

@section('content')
    @php
        $statusStyles = [
            'safe' => 'border-suumo bg-suumo-light',
            'caution' => 'border-amber-400 bg-amber-50',
            'danger' => 'border-price bg-red-50',
        ];
        $statusText = [
            'safe' => 'text-suumo-dark',
            'caution' => 'text-amber-950',
            'danger' => 'text-red-800',
        ];
    @endphp

    <div class="mb-4 text-xs font-bold text-muted">
        <a href="{{ route('simulations.index') }}" class="hover:text-suumo-dark">シミュレーション</a>
        <span class="mx-1">›</span>
        <span class="text-ink">試算結果</span>
    </div>

    <div class="mb-4 flex flex-col gap-4 border border-line bg-white p-4 sm:flex-row sm:items-start sm:justify-between sm:p-5">
        <div>
            <p class="text-[11px] font-bold text-suumo-dark">シミュレーション結果</p>
            <h1 class="mt-1 text-xl font-black text-ink sm:text-2xl">
                {{ $simulation->property?->name ?? '物件未指定の試算' }}
            </h1>
            <p class="mt-2 text-sm text-muted">
                借入 <span class="price-em text-lg">{{ number_format($simulation->borrowing_amount) }}</span>万円 /
                {{ number_format((float) $simulation->interest_rate, 3) }}% /
                {{ $simulation->repayment_years }}年
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('simulations.create', ['property_id' => $simulation->property_id]) }}" class="btn-suumo">条件を変えて再試算</a>
            <form method="POST" action="{{ route('simulations.destroy', $simulation) }}" onsubmit="return confirm('このシミュレーションを削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center border border-red-300 bg-white px-3 py-2 text-sm font-bold text-red-700 hover:bg-red-50">削除</button>
            </form>
            <a href="{{ route('simulations.index') }}" class="btn-suumo-outline">履歴へ</a>
        </div>
    </div>

    <section class="mb-5 border p-5 {{ $statusStyles[$simulation->screening_status] ?? 'border-line bg-white' }}">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-2xl font-black {{ $statusText[$simulation->screening_status] ?? 'text-ink' }}">
                審査シグナル：{{ $simulation->statusLabel() }}
            </h2>
            @include('simulations._status_badge', ['status' => $simulation->screening_status])
        </div>
        <p class="mt-3 text-sm leading-relaxed {{ $statusText[$simulation->screening_status] ?? 'text-ink' }}">
            {{ $simulation->screening_advice }}
        </p>
        <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white/70 p-3">
                <dt class="text-xs font-bold text-muted">返済負担率（適用金利）</dt>
                <dd class="mt-1 text-2xl font-black tabular-nums">{{ number_format((float) $simulation->repayment_ratio_percent, 2) }}%</dd>
            </div>
            <div class="bg-white/70 p-3">
                <dt class="text-xs font-bold text-muted">返済負担率（審査金利）</dt>
                <dd class="mt-1 text-2xl font-black tabular-nums">{{ number_format((float) $simulation->stress_repayment_ratio_percent, 2) }}%</dd>
            </div>
            <div class="bg-white/70 p-3">
                <dt class="text-xs font-bold text-muted">年収</dt>
                <dd class="mt-1 text-xl font-black tabular-nums">{{ number_format($simulation->annual_income) }}万円</dd>
            </div>
            <div class="bg-white/70 p-3">
                <dt class="text-xs font-bold text-muted">雇用 / 勤続</dt>
                <dd class="mt-1 text-xl font-black">{{ $simulation->employmentLabel() }} / {{ $simulation->years_of_service }}年</dd>
            </div>
        </dl>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="border border-line bg-white">
            <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">詳細返済シミュレーション</h2>
            <div class="p-4 sm:p-5">
                <p class="text-xs text-muted">元利均等返済・円単位四捨五入</p>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold text-muted">月々のローン返済額</dt>
                        <dd class="mt-1 text-2xl font-black tabular-nums">{{ number_format($simulation->monthly_payment_yen) }}<span class="ml-1 text-sm font-bold text-muted">円</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-muted">実際の毎月引き落とし総額</dt>
                        <dd class="price-em mt-1 text-2xl">{{ number_format($simulation->actual_monthly_outflow_yen) }}<span class="ml-1 text-sm font-bold">円</span></dd>
                        <p class="mt-1 text-xs text-muted">ローン + 管理費 + 修繕積立金 + 駐車場</p>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-muted">総返済額</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums">{{ number_format($simulation->total_repayment_yen) }}円</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-muted">総利息</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums">{{ number_format($simulation->total_interest_yen) }}円</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-muted">年間ローン返済額</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums">{{ number_format($simulation->annual_loan_repayment_yen) }}円</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-muted">ボーナス時加算</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums">{{ number_format($simulation->bonus_payment_amount) }}万円 / 回</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="border border-line bg-white">
            <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">入力条件</h2>
            <div class="p-4 sm:p-5">
                <table class="spec-table">
                    <tbody>
                        <tr><th>管理費</th><td class="tabular-nums">{{ number_format($simulation->management_fee) }}円/月</td></tr>
                        <tr><th>修繕積立金</th><td class="tabular-nums">{{ number_format($simulation->repair_reserve_fee) }}円/月</td></tr>
                        <tr><th>駐車場代</th><td class="tabular-nums">{{ number_format($simulation->parking_fee) }}円/月</td></tr>
                        <tr><th>既存年間返済</th><td class="tabular-nums">{{ number_format($simulation->existing_annual_repayment) }}万円</td></tr>
                        <tr><th>審査金利</th><td class="tabular-nums">{{ number_format((float) $simulation->review_interest_rate, 3) }}%</td></tr>
                        @if ($simulation->property)
                            <tr>
                                <th>物件詳細</th>
                                <td>
                                    <a href="{{ route('properties.show', $simulation->property) }}" class="font-bold text-suumo-dark hover:underline">
                                        {{ $simulation->property->name }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="mt-4 border border-line bg-suumo-muted p-3 text-xs leading-relaxed text-muted">
                    <p class="font-bold text-ink">判定の目安</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4">
                        <li>返済負担率 25%以内 → 安全</li>
                        <li>25%超〜30%以内 → 注意</li>
                        <li>30%超 → 危険</li>
                        <li>審査金利ストレステストと雇用・勤続も加味</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
@endsection
