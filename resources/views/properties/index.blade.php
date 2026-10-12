@extends('layouts.app')

@section('title', '候補物件')

@section('content')
    <div class="mb-5">
        <h1 class="section-title text-xl sm:text-2xl">候補物件</h1>
    </div>

    @if ($properties->isEmpty())
        <div class="border border-dashed border-line bg-white px-6 py-16 text-center">
            <p class="text-xl font-bold text-ink">該当する物件がありません</p>
            <a href="{{ route('properties.create') }}" class="btn-suumo mt-6">物件を追加する</a>
        </div>
    @else
        @php
            $mapMarkers = $properties->getCollection()
                ->filter(fn ($property) => $property->hasCoordinates())
                ->map(fn ($property) => [
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                    'title' => $property->name,
                    'price' => $property->price,
                    'url' => route('properties.show', $property),
                ])
                ->values();
        @endphp

        @if ($mapMarkers->isNotEmpty())
            @php $indexMapId = 'properties-index-map'; @endphp
            <section class="mb-5 border border-line bg-white">
                <div class="flex items-center justify-between border-b border-line bg-suumo-muted px-4 py-2">
                    <h2 class="text-sm font-bold text-ink">地図</h2>
                    <span class="text-[11px] font-bold text-suumo-dark">ピン {{ $mapMarkers->count() }}件</span>
                </div>
                <div data-map-placeholder class="flex min-h-[200px] flex-col items-center justify-center gap-3 bg-canvas px-4 py-10">
                    <p class="text-sm text-muted">初期表示を速くするため、地図は必要時のみ読み込みます。</p>
                    <button type="button" class="btn-suumo" data-load-map="{{ $indexMapId }}">地図を表示</button>
                </div>
                <div class="hidden" data-map-shell>
                    <x-map :id="$indexMapId" :markers="$mapMarkers" height="340px" :zoom="12" :defer="true" class="border-0" />
                </div>
            </section>
        @endif

        <div class="mb-3 flex items-center justify-between text-sm">
            <p>
                <span class="font-bold text-price">{{ $properties->total() }}</span>
                <span class="text-muted">件</span>
            </p>
            <p class="text-xs text-muted">新しい順</p>
        </div>

        <div class="space-y-3">
            @foreach ($properties as $property)
                <article class="cassette transition hover:border-suumo">
                    <a href="{{ route('properties.show', $property) }}" class="cassette-thumb">
                        @if ($property->evaluation)
                            <span class="absolute left-3 top-3 z-10 bg-suumo px-1.5 py-0.5 text-[10px] font-black text-white">評価{{ $property->evaluation }}</span>
                        @endif
                        @if ($property->coverImage)
                            <img src="{{ $property->coverImage->url() }}" alt="{{ $property->name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                        @else
                            <span class="relative">NO IMAGE</span>
                        @endif
                    </a>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h2 class="min-w-0 truncate text-base font-bold leading-snug sm:text-lg">
                                <a href="{{ route('properties.show', $property) }}" class="text-ink hover:text-suumo-dark hover:underline">
                                    {{ $property->name }}
                                </a>
                            </h2>
                            <a href="{{ route('properties.edit', $property) }}" class="btn-suumo-outline shrink-0 text-xs">編集</a>
                        </div>

                        <p class="price-em mt-2 text-2xl sm:text-3xl">
                            {{ number_format($property->price) }}
                            <span class="text-base font-bold">万円</span>
                        </p>

                        <table class="spec-table mt-3">
                            <tbody>
                                <tr>
                                    <th>所在地</th>
                                    <td>{{ $property->fullAddress() }}</td>
                                </tr>
                                <tr>
                                    <th>間取り</th>
                                    <td>{{ $property->floor_plan ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>駅徒歩</th>
                                    <td>{{ $property->station_minutes }}分</td>
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
                            </tbody>
                        </table>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('properties.show', $property) }}" class="btn-suumo text-xs">詳細を見る</a>
                            <a href="{{ route('simulations.create', ['property_id' => $property->id]) }}" class="btn-suumo-outline text-xs">ローンを試算</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $properties->links() }}
        </div>
    @endif
@endsection
