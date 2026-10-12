@extends('layouts.landing')

@section('title', 'トップ')

@section('content')
    <div class="landing">
        <div class="landing__glow" aria-hidden="true"></div>

        <main class="landing__stage">
            <a href="{{ route('home') }}" class="landing__brand" aria-label="LOAN'S トップへ">
                <span class="landing__mark" aria-hidden="true">
                    <span class="landing__mark-cell is-a"></span>
                    <span class="landing__mark-cell is-b"></span>
                    <span class="landing__mark-cell is-c"></span>
                    <span class="landing__mark-cell is-d"></span>
                </span>
                <span class="landing__name">
                    <span class="landing__name-loan">LOAN</span><span class="landing__name-s">'S</span>
                </span>
            </a>

            <h1 class="landing__headline">「理想の家」も「現実の返済」も、<br class="sm:hidden">ここで全部見つかる。</h1>
            <p class="landing__lead">候補物件の比較から、審査の厳しさ・毎月の返済額までまるごと見える化します。</p>

            <div class="landing__actions">
                <a href="{{ route('properties.index') }}" class="landing__btn landing__btn--primary">候補物件へ</a>
                <a href="{{ route('simulations.create') }}" class="landing__btn landing__btn--secondary">ローンを試算</a>
            </div>
        </main>
    </div>
@endsection
