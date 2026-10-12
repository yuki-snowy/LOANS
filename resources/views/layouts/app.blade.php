<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '物件一覧') — {{ config('app.name', "LOAN'S") }}</title>
    @fonts
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <script
        defer
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="h-1 bg-suumo"></div>

    <header class="border-b border-line bg-white shadow-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('properties.index') }}" class="min-w-0">
                <p class="text-2xl font-black tracking-tight text-suumo">LOAN'S</p>
                <p class="truncate text-[11px] font-medium text-muted">住宅購入・ローン比較管理</p>
            </a>
            <nav class="flex flex-wrap items-center justify-end gap-1 text-sm font-bold sm:gap-2">
                <a href="{{ route('properties.index') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">物件を探す</a>
                <a href="{{ route('simulations.index') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">シミュレーション</a>
                <a href="{{ route('simulations.create') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">ローン試算</a>
                <a href="{{ route('properties.create') }}" class="btn-suumo ml-1">物件を掲載</a>
            </nav>
        </div>
        <div class="bg-suumo-light">
            <div class="mx-auto flex max-w-5xl flex-wrap gap-x-4 gap-y-1 px-4 py-2 text-xs font-bold text-suumo-dark sm:px-6">
                <span>賃貸ではなく購入検討向け</span>
                <span class="text-suumo">｜</span>
                <span>ローン審査シグナル付き</span>
                <span class="text-suumo">｜</span>
                <span>地図は OSM（Cost: ¥0）</span>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        @if (session('success'))
            <div class="mb-5 border border-suumo bg-suumo-light px-4 py-3 text-sm font-medium text-suumo-dark" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <p class="font-bold">入力内容を確認してください。</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-8 border-t border-line bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-xs text-muted sm:px-6">
            <p class="font-bold text-ink">LOAN'S — 住宅購入・ローン比較管理</p>
            <p class="mt-1">Map data © OpenStreetMap contributors (Cost: ¥0) — 有料地図APIは未使用</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
