@extends('layouts.app')

@section('title', 'Восстановление пароля')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm"><div class="card-body p-4">
                    <h1 class="h4 mb-3">Восстановление пароля</h1>
                    <p class="text-secondary small">Укажите email — отправим ссылку для сброса пароля.</p>

                    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить ссылку</button>
                    </form>
                </div></div>
            </div>
        </div>
    </main>
@endsection
