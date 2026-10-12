<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '候補物件') — {{ config('app.name', "LOAN'S") }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="site-header sticky top-0 z-40">
        <div class="h-1 bg-suumo"></div>
        <header class="border-b border-line bg-white/95 shadow-sm backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('properties.index') }}" class="min-w-0">
                    <p class="text-2xl font-black tracking-tight text-suumo">LOAN'S</p>
                </a>
                {{-- PC: ヘッダーナビ / SP: 下部タブバーへ移動 --}}
                <nav class="hidden flex-wrap items-center justify-end gap-1 text-sm font-bold md:flex md:gap-2" aria-label="メインメニュー">
                    <a href="{{ route('properties.index') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">候補物件</a>
                    <a href="{{ route('simulations.index') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">シミュレーション</a>
                    <a href="{{ route('simulations.create') }}" class="px-2 py-1.5 text-ink hover:bg-suumo-muted hover:text-suumo-dark">ローン試算</a>
                    <a href="{{ route('properties.create') }}" class="btn-suumo ml-1">物件を追加</a>
                </nav>
            </div>
        </header>
    </div>

    <main class="mx-auto max-w-5xl px-4 py-6 pb-28 sm:px-6 sm:py-8 md:pb-8">
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

    <footer class="mt-8 hidden border-t border-line bg-white md:block">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-xs text-muted sm:px-6">
            <p class="font-bold text-ink">LOAN'S</p>
            <p class="mt-1">地図: <a href="https://maps.gsi.go.jp/development/ichiran.html" class="underline hover:text-suumo-dark" target="_blank" rel="noopener">国土地理院</a></p>
        </div>
    </footer>

    @include('partials.sp-nav')

    <button
        type="button"
        class="back-to-top"
        data-back-to-top
        aria-label="ページ上部へ戻る"
        hidden
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-5 w-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 14l6-6 6 6"/>
        </svg>
    </button>

    @stack('scripts')
</body>
</html>
