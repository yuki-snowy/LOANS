@extends('layouts.app')

@section('title', 'シミュレーション履歴')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-serif text-3xl text-stone-900">シミュレーション履歴</h1>
            <p class="mt-1 text-sm text-stone-500">返済試算と審査判定の結果を一覧します。</p>
        </div>
        <a href="{{ route('simulations.create') }}" class="inline-flex items-center justify-center bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">
            新規試算
        </a>
    </div>

    @if ($simulations->isEmpty())
        <div class="border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="font-serif text-xl text-stone-800">まだシミュレーションがありません</p>
            <p class="mt-2 text-sm text-stone-500">借入条件と年収を入力して、返済負担と審査シグナルを確認しましょう。</p>
            <a href="{{ route('simulations.create') }}" class="mt-6 inline-flex bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">
                ローンを試算する
            </a>
        </div>
    @else
        <div class="overflow-x-auto border border-stone-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-100 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">日時</th>
                        <th class="px-4 py-3 font-medium">物件</th>
                        <th class="px-4 py-3 font-medium">借入額</th>
                        <th class="px-4 py-3 font-medium">月々返済</th>
                        <th class="px-4 py-3 font-medium">負担率</th>
                        <th class="px-4 py-3 font-medium">判定</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($simulations as $simulation)
                        <tr class="border-b border-stone-100 last:border-0 hover:bg-stone-50">
                            <td class="px-4 py-3 text-stone-600">{{ $simulation->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="max-w-[12rem] truncate px-4 py-3">
                                {{ $simulation->property?->name ?? '物件未指定' }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($simulation->borrowing_amount) }}万円</td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($simulation->monthly_payment_yen) }}円</td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format((float) $simulation->repayment_ratio_percent, 2) }}%</td>
                            <td class="px-4 py-3">
                                @include('simulations._status_badge', ['status' => $simulation->screening_status])
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('simulations.show', $simulation) }}" class="text-teal-800 hover:underline">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $simulations->links() }}
        </div>
    @endif
@endsection
