@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Регистрация</h1>

                        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                       autocomplete="name" class="form-control @error('name') is-invalid @enderror">
                                <div class="invalid-feedback">@error('name') {{ $message }} @else Введите имя. @enderror</div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username (для ссылки на ваши фильмы)</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required
                                       minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" autocomplete="username"
                                       class="form-control @error('username') is-invalid @enderror">
                                <div class="invalid-feedback">@error('username') {{ $message }} @else 3–30 символов: латиница, цифры, «_». @enderror</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                       autocomplete="email" class="form-control @error('email') is-invalid @enderror">
                                <div class="invalid-feedback">@error('email') {{ $message }} @else Введите корректный email. @enderror</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input id="password" type="password" name="password" required minlength="8"
                                       autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                                <div class="invalid-feedback">@error('password') {{ $message }} @else Минимум 8 символов. @enderror</div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Повторите пароль</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                       autocomplete="new-password" class="form-control">
                                <div class="invalid-feedback">Повторите пароль.</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                                <a href="{{ route('login') }}" class="small">Уже есть аккаунт?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
