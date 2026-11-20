@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <main class="container pb-5 pt-3">
        <h1 class="h3 mb-3">Пользователи</h1>

        <div class="list-group">
            @foreach ($users as $u)
                <a href="{{ route('users.show', $u->username) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $u->id === auth()->id() ? 'active' : '' }}">
                    <span>
                        <strong>&#64;{{ $u->username }}</strong>
                        <span class="ms-2 {{ $u->id === auth()->id() ? '' : 'text-secondary' }}">{{ $u->name }}</span>
                        @if ($u->is_admin)<span class="badge text-bg-warning ms-2">admin</span>@endif
                        @if ($u->id === auth()->id())<span class="badge text-bg-light text-dark ms-2">это вы</span>@endif
                    </span>
                    <span class="badge text-bg-secondary rounded-pill">фильмов: {{ $u->films_count }}</span>
                </a>
            @endforeach
        </div>
    </main>
@endsection
