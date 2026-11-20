@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Вход</h1>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       autocomplete="username" class="form-control @error('email') is-invalid @enderror">
                                <div class="invalid-feedback">@error('email') {{ $message }} @else Введите email. @enderror</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input id="password" type="password" name="password" required autocomplete="current-password"
                                       class="form-control @error('password') is-invalid @enderror">
                                <div class="invalid-feedback">@error('password') {{ $message }} @else Введите пароль. @enderror</div>
                            </div>

                            <div class="form-check mb-3">
                                <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
                                <label for="remember_me" class="form-check-label">Запомнить меня</label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">Войти</button>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small">Забыли пароль?</a>
                                @endif
                            </div>
                        </form>

                        <hr>
                        <p class="mb-0 small">Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
