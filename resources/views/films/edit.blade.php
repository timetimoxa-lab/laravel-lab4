@extends('layouts.app')

@section('title', 'Редактировать: ' . $film->title)

@section('content')
    <main class="container py-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h1 class="h3 mb-0">Редактировать «{{ $film->title }}»</h1>
                    <a href="{{ route('films.show', $film) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Назад к фильму
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form id="film-form"
                              action="{{ route('films.update', $film) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              data-check-url="{{ route('films.check-duplicate') }}"
                              data-ignore-id="{{ $film->id }}"
                              novalidate>
                            @csrf
                            @method('PUT')
                            @include('films._form', ['isEdit' => true, 'submitLabel' => 'Сохранить изменения'])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
