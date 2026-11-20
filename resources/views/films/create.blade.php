@extends('layouts.app')

@section('title', 'Добавить фильм')

@section('content')
    <main class="container py-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h1 class="h3 mb-0">Добавить фильм</h1>
                    <a href="{{ route('films.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>К списку
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form id="film-form"
                              action="{{ route('films.store') }}"
                              method="POST"
                              enctype="multipart/form-data"
                              data-check-url="{{ route('films.check-duplicate') }}"
                              data-ignore-id=""
                              novalidate>
                            @csrf
                            @include('films._form', ['isEdit' => false, 'submitLabel' => 'Добавить фильм'])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
