<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '物件一覧') — {{ config('app.name', "LOAN'S") }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('properties.index') }}" class="group">
                <p class="font-serif text-2xl tracking-tight text-teal-800 transition group-hover:text-teal-700">LOAN'S</p>
                <p class="text-xs text-stone-500">住宅購入・ローン比較管理</p>
            </a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('properties.index') }}" class="text-stone-600 transition hover:text-teal-800">物件一覧</a>
                <a href="{{ route('properties.create') }}" class="bg-teal-800 px-3 py-1.5 text-white transition hover:bg-teal-700">新規登録</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        @if (session('success'))
            <div class="mb-6 border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <p class="font-medium">入力内容を確認してください。</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-stone-200 py-6 text-center text-xs text-stone-500">
        Phase 1 — 物件 CRUD &amp; データベース構築
    </footer>

    @stack('scripts')
</body>
</html>
