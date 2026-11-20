@extends('layouts.app')

@section('title', 'Подтверждение email')

@section('content')
    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm"><div class="card-body p-4">
                    <h1 class="h4 mb-3">Подтвердите email</h1>
                    <p class="text-secondary">Мы отправили письмо со ссылкой для подтверждения. Если письмо не пришло, отправьте его ещё раз.</p>
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success">Новая ссылка отправлена.</div>
                    @endif
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('verification.send') }}">@csrf
                            <button class="btn btn-primary">Отправить ещё раз</button>
                        </form>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="btn btn-outline-secondary">Выйти</button>
                        </form>
                    </div>
                </div></div>
            </div>
        </div>
    </main>
@endsection
