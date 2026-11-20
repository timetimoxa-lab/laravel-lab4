@extends('layouts.app')

@section('title', 'Все фильмы')

@section('content')
    <header class="hero container d-flex flex-wrap align-items-end justify-content-between gap-3">
        <div>
            <h1>Фильмография Джека Николсона</h1>
            <p class="lead mb-0">Фильмы всех пользователей. Фильмы конкретного пользователя — в разделе «Пользователи».</p>
        </div>
        @can('create-film')
            <a href="{{ route('films.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>Добавить
            </a>
        @endcan
    </header>

    <main class="container pb-5 mt-3">
        @if ($films->isEmpty())
            <div class="alert alert-info">Фильмов пока нет.</div>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxxl-5 g-4 justify-content-start">
                @foreach ($films as $film)
                    @include('films._card')
                @endforeach
            </div>
        @endif
    </main>
@endsection
