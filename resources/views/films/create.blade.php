@extends('layouts.app')

@section('title', 'Добавить фильм')

@section('content')
    <main class="container pb-5">
        <h1 class="h3 mb-4">Добавить фильм</h1>

        <form method="POST" action="{{ route('films.store') }}" enctype="multipart/form-data"
              class="needs-validation" novalidate>
            @csrf
            @include('films._form')
        </form>
    </main>
@endsection
