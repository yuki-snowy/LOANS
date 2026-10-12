@extends('layouts.app')

@section('title', '物件編集')

@section('content')
    <div class="mb-5">
        <h1 class="section-title">物件情報を編集</h1>
    </div>

    <form method="POST" action="{{ route('properties.update', $property) }}" enctype="multipart/form-data" class="max-w-2xl border border-line bg-white p-5 sm:p-6">
        @csrf
        @method('PUT')
        @include('properties._form')

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="btn-suumo">更新する</button>
            <a href="{{ route('properties.show', $property) }}" class="btn-suumo-outline">キャンセル</a>
        </div>
    </form>
@endsection
