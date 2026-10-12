@php
    $styles = [
        'safe' => 'bg-suumo text-white border-suumo',
        'caution' => 'bg-amber-400 text-amber-950 border-amber-500',
        'danger' => 'bg-price text-white border-price',
    ];
    $labels = \App\Models\Simulation::STATUS_LABELS;
@endphp
<span class="inline-block border px-2 py-0.5 text-xs font-black {{ $styles[$status] ?? 'bg-gray-100 text-ink border-line' }}">
    {{ $labels[$status] ?? $status }}
</span>
