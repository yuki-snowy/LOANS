@extends('layouts.app')

@section('title', '物件一覧')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-serif text-3xl text-stone-900">物件一覧</h1>
            <p class="mt-1 text-sm text-stone-500">検討中の物件を登録・管理します。</p>
        </div>
        <a href="{{ route('properties.create') }}" class="inline-flex items-center justify-center bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">
            物件を登録
        </a>
    </div>

    @if ($properties->isEmpty())
        <div class="border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="font-serif text-xl text-stone-800">まだ物件がありません</p>
            <p class="mt-2 text-sm text-stone-500">最初の物件を登録して、比較を始めましょう。</p>
            <a href="{{ route('properties.create') }}" class="mt-6 inline-flex bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">
                物件を登録する
            </a>
        </div>
    @else
        <div class="overflow-x-auto border border-stone-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-100 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">物件名</th>
                        <th class="px-4 py-3 font-medium">価格</th>
                        <th class="px-4 py-3 font-medium">駅徒歩</th>
                        <th class="px-4 py-3 font-medium">評価</th>
                        <th class="px-4 py-3 font-medium">所在地</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($properties as $property)
                        <tr class="border-b border-stone-100 last:border-0 hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-900">
                                <a href="{{ route('properties.show', $property) }}" class="hover:text-teal-800 hover:underline">
                                    {{ $property->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($property->price) }}万円</td>
                            <td class="px-4 py-3 tabular-nums">{{ $property->station_minutes }}分</td>
                            <td class="px-4 py-3">
                                @if ($property->evaluation)
                                    <span class="inline-block min-w-6 bg-teal-50 px-1.5 py-0.5 text-center text-xs font-semibold text-teal-900">
                                        {{ $property->evaluation }}
                                    </span>
                                @else
                                    <span class="text-stone-400">—</span>
                                @endif
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 text-stone-600">{{ $property->address }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('properties.edit', $property) }}" class="text-teal-800 hover:underline">編集</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $properties->links() }}
        </div>
    @endif
@endsection
