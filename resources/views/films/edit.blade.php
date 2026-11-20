@extends('layouts.app')

@section('title', 'Редактировать: ' . $film->title)

@section('content')
    <main class="container pb-5">
        <h1 class="h3 mb-4">Редактировать фильм</h1>

        <form method="POST" action="{{ route('films.update', $film) }}" enctype="multipart/form-data"
              class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            @include('films._form')
        </form>
    </main>
@endsection
