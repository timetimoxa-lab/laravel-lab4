@extends('layouts.app')

@section('title', 'Фильмы ' . $user->username)

@section('content')
    <main class="container pb-5 pt-3">
        <div class="row g-4">

            {{-- Навигация по пользователям (средствами шаблона) --}}
            <aside class="col-12 col-md-3">
                <h2 class="h6 text-secondary text-uppercase">Пользователи</h2>
                <div class="list-group">
                    @foreach ($allUsers as $u)
                        <a href="{{ route('users.show', $u->username) }}"
                           class="list-group-item list-group-item-action {{ $u->id === $user->id ? 'active' : '' }}">
                            &#64;{{ $u->username }}
                        </a>
                    @endforeach
                </div>
            </aside>

            <section class="col-12 col-md-9">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <h1 class="h3 mb-0">
                        Фильмы &#64;{{ $user->username }}
                        @if ($user->is_admin)<span class="badge text-bg-warning">admin</span>@endif
                    </h1>

                    @if ($user->id === auth()->id())
                        <a href="{{ route('films.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>Добавить фильм
                        </a>
                    @endif
                </div>

                <p class="text-secondary">{{ $user->name }}</p>

                @if (auth()->user()->is_admin)
                    <div class="alert alert-warning py-2 small">
                        Вы администратор: в списке показаны и мягко удалённые фильмы пользователя.
                    </div>
                @endif

                @if ($films->isEmpty())
                    <div class="alert alert-info">У пользователя пока нет фильмов.</div>
                @else
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4 justify-content-start">
                        @foreach ($films as $film)
                            @include('films._card')
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
