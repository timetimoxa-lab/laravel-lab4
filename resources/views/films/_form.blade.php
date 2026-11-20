{{-- Общая форма для create и edit. В edit доступна переменная $film --}}
@php($f = $film ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="title" class="form-label">Название</label>
        <input type="text" id="title" name="title" maxlength="255" required
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $f?->title) }}">
        <div class="invalid-feedback">@error('title') {{ $message }} @else Укажите название фильма. @enderror</div>
    </div>

    <div class="col-md-6">
        <label for="original_title" class="form-label">Оригинальное название</label>
        <input type="text" id="original_title" name="original_title" maxlength="255"
               class="form-control @error('original_title') is-invalid @enderror"
               value="{{ old('original_title', $f?->original_title) }}">
        <div class="invalid-feedback">@error('original_title') {{ $message }} @enderror</div>
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Роль</label>
        <input type="text" id="role" name="role" maxlength="255" required
               class="form-control @error('role') is-invalid @enderror"
               value="{{ old('role', $f?->role) }}">
        <div class="invalid-feedback">@error('role') {{ $message }} @else Укажите роль. @enderror</div>
    </div>

    <div class="col-md-6">
        <label for="genre" class="form-label">Жанр</label>
        <input type="text" id="genre" name="genre" maxlength="255" required
               class="form-control @error('genre') is-invalid @enderror"
               value="{{ old('genre', $f?->genre) }}">
        <div class="invalid-feedback">@error('genre') {{ $message }} @else Укажите жанр. @enderror</div>
    </div>

    <div class="col-md-6">
        <label for="released_at" class="form-label">Дата выхода</label>
        <input type="date" id="released_at" name="released_at" required
               min="1900-01-01" max="{{ now()->format('Y-m-d') }}"
               class="form-control @error('released_at') is-invalid @enderror"
               value="{{ old('released_at', $f?->released_at?->format('Y-m-d')) }}">
        <div class="invalid-feedback">@error('released_at') {{ $message }} @else Укажите корректную дату выхода. @enderror</div>
    </div>

    <div class="col-md-6">
        <label for="accent_color" class="form-label">Цвет карточки</label>
        <input type="color" id="accent_color" name="accent_color" required
               class="form-control form-control-color @error('accent_color') is-invalid @enderror"
               value="{{ old('accent_color', $f?->accent_color ?? '#3b4cca') }}">
        <div class="invalid-feedback">@error('accent_color') {{ $message }} @enderror</div>
    </div>

    <div class="col-12">
        <label for="short_description" class="form-label">Краткое описание (для карточки)</label>
        <input type="text" id="short_description" name="short_description" maxlength="500" required
               class="form-control @error('short_description') is-invalid @enderror"
               value="{{ old('short_description', $f?->short_description) }}">
        <div class="invalid-feedback">@error('short_description') {{ $message }} @else Заполните краткое описание. @enderror</div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Описание</label>
        <textarea id="description" name="description" rows="5" maxlength="5000" required
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $f?->description) }}</textarea>
        <div class="invalid-feedback">@error('description') {{ $message }} @else Заполните описание. @enderror</div>
    </div>

    <div class="col-12">
        <label for="poster" class="form-label">Постер (jpg/png, до 4 МБ; будет приведён к 400×600)</label>
        @if ($f)
            <div class="mb-2">
                <img src="{{ $f->poster_url }}" alt="Текущий постер" class="rounded" style="height: 150px;">
                <div class="form-text">Текущий постер. Выберите файл, только если хотите его заменить.</div>
            </div>
        @endif
        <input type="file" id="poster" name="poster" accept="image/jpeg,image/png"
               class="form-control @error('poster') is-invalid @enderror" {{ $f ? '' : 'required' }}>
        <div class="invalid-feedback">@error('poster') {{ $message }} @else Выберите изображение постера. @enderror</div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk me-1"></i>Сохранить
    </button>
    <a href="{{ $f ? route('films.show', $f) : route('films.index') }}" class="btn btn-outline-secondary">Отмена</a>
</div>
