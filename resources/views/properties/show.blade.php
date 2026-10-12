@extends('layouts.app')

@section('title', $property->name)

@section('content')
    <div class="mb-4 text-xs font-bold text-muted">
        <a href="{{ route('properties.index') }}" class="hover:text-suumo-dark">候補物件</a>
        <span class="mx-1">›</span>
        <span class="text-ink">物件詳細</span>
    </div>

    <div class="mb-4 border border-line bg-white">
        <div class="border-b border-line bg-suumo-muted px-4 py-3 sm:px-5">
            <h1 class="text-xl font-black leading-snug text-ink sm:text-2xl">{{ $property->name }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $property->fullAddress() }}</p>
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

    <section class="mb-4 border border-line bg-white">
        <h2 class="border-b border-line bg-suumo-muted px-4 py-2 text-sm font-bold">物件画像</h2>
        @if ($property->images->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($property->images as $image)
                    <a href="{{ $image->url() }}" target="_blank" rel="noopener noreferrer" class="group block overflow-hidden border border-line bg-suumo-muted">
                        <img
                            src="{{ $image->url() }}"
                            alt="{{ $property->name }} 画像{{ $loop->iteration }}"
                            class="aspect-[4/3] h-full w-full object-cover transition group-hover:opacity-90"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            decoding="async"
                        >
                    </a>
                @endforeach
            </div>
        @else
            <div class="flex min-h-40 items-center justify-center px-4 py-10 text-sm font-bold text-muted">
                NO IMAGE
            </div>
        @endif
    </section>

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
                            <th>間取り</th>
                            <td>{{ $property->floor_plan ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>築年</th>
                            <td>{{ $property->built_year ? $property->built_year.'年' : '—' }}</td>
                        </tr>
                        <tr>
                            <th>専有面積</th>
                            <td>
                                @if ($property->exclusive_area)
                                    {{ number_format((float) $property->exclusive_area, 2) }}㎡
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>構造・階建て</th>
                            <td>{{ $property->structureLabel() }}</td>
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
                            <th>郵便番号</th>
                            <td>{{ $property->formattedPostalCode() ?? '未設定' }}</td>
                        </tr>
                        <tr>
                            <th>都道府県</th>
                            <td>{{ $property->prefecture }}</td>
                        </tr>
                        <tr>
                            <th>住所</th>
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
