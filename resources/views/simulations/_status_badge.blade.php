@php
    $styles = [
        'safe' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'caution' => 'bg-amber-50 text-amber-900 border-amber-200',
        'danger' => 'bg-red-50 text-red-800 border-red-200',
    ];
    $labels = \App\Models\Simulation::STATUS_LABELS;
@endphp
<span class="inline-block border px-2 py-0.5 text-xs font-semibold {{ $styles[$status] ?? 'bg-stone-50 text-stone-700 border-stone-200' }}">
    {{ $labels[$status] ?? $status }}
</span>
