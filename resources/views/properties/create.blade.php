@extends('layouts.app')

@section('title', '物件を追加')

@section('content')
    <div class="mb-5">
        <h1 class="section-title">物件を追加</h1>
    </div>

    <form method="POST" action="{{ route('properties.store') }}" class="max-w-2xl border border-line bg-white p-5 sm:p-6">
        @csrf
        @include('properties._form')

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="btn-suumo">追加する</button>
            <a href="{{ route('properties.index') }}" class="btn-suumo-outline">キャンセル</a>
        </div>
    </form>
@endsection
