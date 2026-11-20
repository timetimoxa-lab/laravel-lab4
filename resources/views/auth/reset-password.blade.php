@extends('layouts.app')

@section('title', 'Новый пароль')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm"><div class="card-body p-4">
                    <h1 class="h4 mb-3">Новый пароль</h1>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input id="password" type="password" name="password" required
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Повторите пароль</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Сменить пароль</button>
                    </form>
                </div></div>
            </div>
        </div>
    </main>
@endsection
