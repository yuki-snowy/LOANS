@props([
    'latitude' => null,
    'longitude' => null,
    'title' => null,
    'price' => null,
    'height' => '400px',
    'zoom' => 15,
    'markers' => [],
    'interactive' => false,
    'latInput' => null,
    'lngInput' => null,
    'showCostBadge' => true,
])

@php
    $markerPayload = collect($markers)->map(function ($marker) {
        return [
            'latitude' => $marker['latitude'] ?? $marker->latitude ?? null,
            'longitude' => $marker['longitude'] ?? $marker->longitude ?? null,
            'title' => $marker['title'] ?? $marker->name ?? null,
            'price' => $marker['price'] ?? $marker->price ?? null,
            'url' => $marker['url'] ?? null,
        ];
    })->values();

    $hasSinglePoint = filled($latitude) && filled($longitude);
    $hasMarkers = $markerPayload->isNotEmpty();
    $mapId = $attributes->get('id') ?: 'loans-map-'.uniqid();
@endphp

<div {{ $attributes->class(['relative overflow-hidden border border-stone-200 bg-stone-100']) }}>
    <div
        id="{{ $mapId }}"
        data-loans-map
        data-latitude="{{ $hasSinglePoint ? $latitude : '' }}"
        data-longitude="{{ $hasSinglePoint ? $longitude : '' }}"
        data-title="{{ $title }}"
        data-price="{{ $price }}"
        data-zoom="{{ $zoom }}"
        data-markers='@json($markerPayload)'
        data-interactive="{{ $interactive ? '1' : '0' }}"
        data-lat-input="{{ $latInput }}"
        data-lng-input="{{ $lngInput }}"
        class="w-full"
        style="height: {{ $height }}; min-height: 240px;"
        role="img"
        aria-label="OpenStreetMap による物件位置地図"
    ></div>

    @if ($showCostBadge)
        <p class="pointer-events-none absolute bottom-2 left-2 z-[500] bg-white/90 px-2 py-1 text-[10px] text-stone-600 shadow-sm">
            Map data © OpenStreetMap contributors (Cost: ¥0)
        </p>
    @endif

    @unless ($hasSinglePoint || $hasMarkers || $interactive)
        <div class="absolute inset-0 z-[400] flex items-center justify-center bg-stone-50/90 px-4 text-center text-sm text-stone-500">
            表示できる座標がありません
        </div>
    @endunless
</div>
