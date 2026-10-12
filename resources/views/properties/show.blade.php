@extends('layouts.app')

@section('title', $property->name)

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-stone-500">物件詳細</p>
            <h1 class="mt-1 font-serif text-3xl text-stone-900">{{ $property->name }}</h1>
            <p class="mt-2 text-sm text-stone-600">{{ $property->address }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('simulations.create', ['property_id' => $property->id]) }}" class="bg-teal-800 px-4 py-2 text-sm text-white transition hover:bg-teal-700">ローン試算</a>
            <a href="{{ route('properties.edit', $property) }}" class="border border-teal-800 px-4 py-2 text-sm text-teal-800 transition hover:bg-teal-50">編集</a>
            <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('この物件を削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="border border-red-300 px-4 py-2 text-sm text-red-700 transition hover:bg-red-50">削除</button>
            </form>
            <a href="{{ route('properties.index') }}" class="border border-stone-300 px-4 py-2 text-sm text-stone-700 transition hover:bg-stone-50">一覧へ</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <dl class="grid gap-4 border border-stone-200 bg-white p-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-stone-500">価格</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-stone-900">{{ number_format($property->price) }}<span class="ml-1 text-sm font-normal text-stone-500">万円</span></dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">駅徒歩</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-stone-900">{{ $property->station_minutes }}<span class="ml-1 text-sm font-normal text-stone-500">分</span></dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">評価</dt>
                <dd class="mt-1 text-lg font-semibold text-stone-900">
                    {{ $property->evaluation ?? '未設定' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-stone-500">座標</dt>
                <dd class="mt-1 text-sm tabular-nums text-stone-700">
                    @if ($property->hasCoordinates())
                        {{ $property->latitude }}, {{ $property->longitude }}
                    @else
                        未設定
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-stone-500">メモ</dt>
                <dd class="mt-1 whitespace-pre-wrap text-sm text-stone-700">{{ $property->memo ?: '—' }}</dd>
            </div>
        </dl>

        <div class="border border-stone-200 bg-white">
            @if ($property->hasCoordinates())
                <x-map
                    :latitude="$property->latitude"
                    :longitude="$property->longitude"
                    :title="$property->name"
                    :price="$property->price"
                    height="100%"
                    class="h-80 lg:h-full lg:min-h-80"
                />
            @else
                <div class="p-4">
                    <p class="mb-3 text-sm text-stone-500">緯度・経度が未設定のため、東京駅周辺をフォールバック表示しています。編集画面で地図上のピンを調整できます。</p>
                    <x-map
                        :latitude="35.681236"
                        :longitude="139.767125"
                        title="東京駅（デフォルト中心）"
                        :zoom="13"
                        height="320px"
                    />
                </div>
            @endif
        </div>
    </div>
@endsection
