@php
    $items = [
        [
            'label' => '候補物件',
            'route' => 'properties.index',
            'active' => request()->routeIs('properties.index', 'properties.show', 'properties.edit'),
            'icon' => 'home',
        ],
        [
            'label' => 'シミュレーション',
            'route' => 'simulations.index',
            'active' => request()->routeIs('simulations.index', 'simulations.show'),
            'icon' => 'chart',
        ],
        [
            'label' => 'ローン試算',
            'route' => 'simulations.create',
            'active' => request()->routeIs('simulations.create'),
            'icon' => 'calc',
        ],
        [
            'label' => '物件を追加',
            'route' => 'properties.create',
            'active' => request()->routeIs('properties.create'),
            'icon' => 'plus',
        ],
    ];
@endphp

<nav class="sp-tabbar md:hidden" aria-label="メインメニュー">
    @foreach ($items as $item)
        <a
            href="{{ route($item['route']) }}"
            class="sp-tabbar__item {{ $item['active'] ? 'is-active' : '' }}"
            @if ($item['active']) aria-current="page" @endif
        >
            <span class="sp-tabbar__icon" aria-hidden="true">
                @switch($item['icon'])
                    @case('home')
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path d="M12 3.2 3.5 10.2a1 1 0 0 0-.3.7V20a1 1 0 0 0 1 1h5.2v-5.5h5.2V21H19.8a1 1 0 0 0 1-1v-9.1a1 1 0 0 0-.3-.7L12 3.2Z"/>
                        </svg>
                        @break
                    @case('chart')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M10 19V10M16 19v-6M22 19H2"/>
                        </svg>
                        @break
                    @case('calc')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                            <rect x="5" y="3.5" width="14" height="17" rx="2"/>
                            <path stroke-linecap="round" d="M8 8h8M8 12h2M12 12h2M16 12h0.01M8 16h2M12 16h2M16 16h0.01"/>
                        </svg>
                        @break
                    @case('plus')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <path stroke-linecap="round" d="M12 8v8M8 12h8"/>
                        </svg>
                        @break
                @endswitch
            </span>
            <span class="sp-tabbar__label">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
