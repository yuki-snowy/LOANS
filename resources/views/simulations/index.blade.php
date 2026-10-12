@extends('layouts.app')

@section('title', 'シミュレーション履歴')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <h1 class="section-title">シミュレーション履歴</h1>
        <a href="{{ route('simulations.create') }}" class="btn-suumo">新規試算</a>
    </div>

    @if ($simulations->isEmpty())
        <div class="border border-dashed border-line bg-white px-6 py-16 text-center">
            <p class="text-xl font-bold text-ink">シミュレーションがありません</p>
            <a href="{{ route('simulations.create') }}" class="btn-suumo mt-6">ローンを試算する</a>
        </div>
    @else
        <div class="overflow-x-auto border border-line bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-line bg-suumo-muted text-xs font-bold text-ink">
                    <tr>
                        <th class="px-4 py-3">日時</th>
                        <th class="px-4 py-3">物件</th>
                        <th class="px-4 py-3">借入額</th>
                        <th class="px-4 py-3">月々返済</th>
                        <th class="px-4 py-3">負担率</th>
                        <th class="px-4 py-3">判定</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($simulations as $simulation)
                        <tr class="border-b border-line last:border-0 hover:bg-suumo-muted/50">
                            <td class="px-4 py-3 text-muted">{{ $simulation->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="max-w-[12rem] truncate px-4 py-3 font-bold">
                                {{ $simulation->property?->name ?? '物件未指定' }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($simulation->borrowing_amount) }}万円</td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($simulation->monthly_payment_yen) }}円</td>
                            <td class="px-4 py-3 tabular-nums font-bold">{{ number_format((float) $simulation->repayment_ratio_percent, 2) }}%</td>
                            <td class="px-4 py-3">
                                @include('simulations._status_badge', ['status' => $simulation->screening_status])
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('simulations.show', $simulation) }}" class="font-bold text-suumo-dark hover:underline">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($simulations->hasPages())
            <div class="pagination-bar mt-6">
                {{ $simulations->links() }}
            </div>
        @endif
    @endif
@endsection
