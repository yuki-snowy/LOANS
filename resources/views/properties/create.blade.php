@extends('layouts.app')

@section('title', '物件登録')

@section('content')
    <div class="mb-8">
        <h1 class="font-serif text-3xl text-stone-900">物件登録</h1>
        <p class="mt-1 text-sm text-stone-500">検討物件の基本情報を入力します。</p>
    </div>

    <form method="POST" action="{{ route('properties.store') }}" class="max-w-2xl border border-stone-200 bg-white p-6">
        @csrf
        @include('properties._form')

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="bg-teal-800 px-5 py-2 text-sm text-white transition hover:bg-teal-700">
                登録する
            </button>
            <a href="{{ route('properties.index') }}" class="border border-stone-300 px-5 py-2 text-sm text-stone-700 transition hover:bg-stone-50">
                キャンセル
            </a>
        </div>
    </form>
@endsection
