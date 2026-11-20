<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Фильмография Джека Николсона')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>

    <nav class="navbar navbar-expand-md navbar-dark sticky-top">
        <div class="container-fluid container-xxxl">
            <a class="navbar-brand" href="{{ route('films.index') }}"><i class="fa-solid fa-film me-2"></i>Джек Николсон</a>

            <!-- Единственная кнопка «Загрузить» -->
            <button type="button" class="btn btn-outline-light btn-download ms-auto ms-md-3 me-2 me-md-0 order-md-last">
                <i class="fa-solid fa-upload me-1"></i>Загрузить
            </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="nav navbar-nav ms-md-auto align-items-md-center">
                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ route('films.index') }}">Все фильмы</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Пользователи</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('films.create') }}">Добавить фильм</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-user me-1"></i>{{ auth()->user()->username }}
                                @if (auth()->user()->is_admin)<span class="badge text-bg-warning ms-1">admin</span>@endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('users.show', auth()->user()->username) }}">Мои фильмы</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Выйти</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Войти</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container pt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
        @endif
        @if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated']))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif
    </div>

    @yield('content')

    <footer class="container py-4 text-center text-secondary small">ЛР 4 · Вариант 14 · Laravel 12 + Breeze + Bootstrap</footer>

    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="downloadToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fa-solid fa-spinner toast-spinner me-2"></i>
                <strong class="me-auto">Загрузка</strong>
                <small>сейчас</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
            </div>
            <div class="toast-body">Функционал загрузки на текущий момент недоступен.</div>
        </div>
    </div>

</body>
</html>
