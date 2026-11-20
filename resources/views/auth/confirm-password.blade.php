@extends('layouts.app')

@section('title', 'Подтверждение пароля')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm"><div class="card-body p-4">
                    <h1 class="h4 mb-3">Подтвердите пароль</h1>
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Подтвердить</button>
                    </form>
                </div></div>
            </div>
        </div>
    </main>
@endsection
