@extends('layouts.app')

@section('title', $property->name)

@section('content')
    <div class="mb-4 text-xs font-bold text-muted">
        <a href="{{ route('properties.index') }}" class="hover:text-suumo-dark">物件を探す</a>
        <span class="mx-1">›</span>
        <span class="text-ink">物件詳細</span>
    </div>

    <div class="mb-4 border border-line bg-white">
        <div class="border-b border-line bg-suumo-muted px-4 py-3 sm:px-5">
            <p class="text-[11px] font-bold text-suumo-dark">中古マンション</p>
            <h1 class="mt-1 text-xl font-black leading-snug text-ink sm:text-2xl">{{ $property->name }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $property->address }}</p>
        </div>

        <div class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-5">
            <div>
                <p class="text-xs font-bold text-muted">販売価格</p>
                <p class="price-em text-4xl">
                    {{ number_format($property->price) }}
                    <span class="text-lg font-bold">万円</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('simulations.create', ['property_id' => $property->id]) }}" class="btn-suumo">ローン試算</a>
                <a href="{{ route('properties.edit', $property) }}" class="btn-suumo-outline">編集</a>
                <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('この物件を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center border border-red-300 bg-white px-3 py-2 text-sm font-bold text-red-700 hover:bg-red-50">削除</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="border border-line bg-white">
            <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">物件概要</h2>
            <div class="p-4">
                <table class="spec-table">
                    <tbody>
                        <tr>
                            <th>価格</th>
                            <td><span class="price-em text-xl">{{ number_format($property->price) }}</span> 万円</td>
                        </tr>
                        <tr>
                            <th>駅徒歩</th>
                            <td>{{ $property->station_minutes }}分</td>
                        </tr>
                        <tr>
                            <th>評価</th>
                            <td>
                                @if ($property->evaluation)
                                    <span class="inline-block bg-suumo px-2 py-0.5 text-xs font-black text-white">{{ $property->evaluation }}</span>
                                @else
                                    未設定
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>所在地</th>
                            <td>{{ $property->address }}</td>
                        </tr>
                        <tr>
                            <th>座標</th>
                            <td class="tabular-nums">
                                @if ($property->hasCoordinates())
                                    {{ $property->latitude }}, {{ $property->longitude }}
                                @else
                                    未設定
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>メモ</th>
                            <td class="whitespace-pre-wrap">{{ $property->memo ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="border border-line bg-white">
            <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">周辺地図</h2>
            @if ($property->hasCoordinates())
                <x-map
                    :latitude="$property->latitude"
                    :longitude="$property->longitude"
                    :title="$property->name"
                    :price="$property->price"
                    height="100%"
                    class="h-80 border-0 lg:h-full lg:min-h-80"
                />
            @else
                <x-map
                    :latitude="35.681236"
                    :longitude="139.767125"
                    title="東京駅"
                    :zoom="13"
                    height="320px"
                    class="border-0"
                />
            @endif
        </section>
    </div>
@endsection
