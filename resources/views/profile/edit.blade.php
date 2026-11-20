@extends('layouts.app')

@section('title', 'Профиль')

@section('content')
    <main class="container pb-5">
        <h1 class="h3 mb-4">Профиль</h1>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h2 class="h5">Данные профиля</h2>
                    <p class="small text-secondary mb-3">Username: <strong>&#64;{{ $user->username }}</strong> (изменить нельзя — на него ведут ссылки)</p>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success py-2">Сохранено.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                </div></div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h2 class="h5">Смена пароля</h2>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success py-2">Пароль изменён.</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input id="current_password" type="password" name="current_password" required
                                   class="form-control @if ($errors->updatePassword->has('current_password')) is-invalid @endif">
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input id="new_password" type="password" name="password" required
                                   class="form-control @if ($errors->updatePassword->has('password')) is-invalid @endif">
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Повторите новый пароль</label>
                            <input id="new_password_confirmation" type="password" name="password_confirmation" required class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Сменить пароль</button>
                    </form>
                </div></div>
            </div>

            <div class="col-12">
                <div class="card border-danger"><div class="card-body">
                    <h2 class="h5 text-danger">Удаление аккаунта</h2>
                    <p class="small text-secondary">Аккаунт и все его фильмы и комментарии будут удалены. Для подтверждения введите пароль.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="row g-2 align-items-start"
                          onsubmit="return confirm('Удалить аккаунт безвозвратно?')">
                        @csrf
                        @method('DELETE')
                        <div class="col-md-5">
                            <input type="password" name="password" placeholder="Пароль" required
                                   class="form-control @if ($errors->userDeletion->has('password')) is-invalid @endif">
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-danger">Удалить аккаунт</button>
                        </div>
                    </form>
                </div></div>
            </div>
        </div>
    </main>
@endsection
