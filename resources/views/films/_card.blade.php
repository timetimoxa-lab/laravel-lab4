{{-- Карточка фильма. Кнопки показываются только при наличии прав (@can -> Gates) --}}
@php($oscar = $film->awards->firstWhere('award_name', 'Оскар'))
<div class="col">
    <article class="card object-card h-100 {{ $film->trashed() ? 'border-danger' : '' }}"
             style="--theme-main: {{ $film->accent_color }}; --theme-contrast: {{ $film->accent_text_color }}">
        <div class="card-media position-relative">
            <img src="{{ $film->poster_url }}" class="card-img-top img-fluid {{ $film->trashed() ? 'opacity-50' : '' }}"
                 alt="Постер фильма «{{ $film->title }}»">
            <h2 class="card-title card-label h6 mb-0">{{ $film->title }}</h2>
            @if ($film->trashed())
                <span class="badge text-bg-danger position-absolute top-0 end-0 m-2">Удалён</span>
            @endif
        </div>
        <div class="card-body">
            <p class="mb-1 text-secondary small">{{ $film->release_year }} · {{ $film->genre }}</p>
            <p class="mb-1 small"><strong>Роль:</strong> {{ $film->role }}</p>
            <p class="mb-2 small">
                <i class="fa-solid fa-user me-1 text-secondary"></i>
                @if ($film->user)
                    <a href="{{ route('users.show', $film->user->username) }}">&#64;{{ $film->user->username }}</a>
                @else
                    <span class="text-secondary">владелец удалён</span>
                @endif
            </p>
            <p class="card-text">{{ $film->short_description }}</p>
            @if ($oscar)
                <p class="mb-0">
                    <span class="me-1">Оскар:</span>
                    @if ($oscar->is_winner)
                        <span class="badge text-bg-success">Победа</span>
                    @else
                        <span class="badge text-bg-secondary">Номинация</span>
                    @endif
                </p>
            @endif
        </div>
        <div class="card-footer bg-transparent border-0 pb-3 d-flex flex-wrap gap-2">
            <a href="{{ route('films.show', $film) }}" class="btn btn-details">Подробнее</a>

            @if (! $film->trashed())
                @can('update-film', $film)
                    <a href="{{ route('films.edit', $film) }}" class="btn btn-outline-secondary" title="Редактировать">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                @endcan
                @can('delete-film', $film)
                    <form action="{{ route('films.destroy', $film) }}" method="POST"
                          onsubmit="return confirm('Удалить фильм «{{ $film->title }}»?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" title="Удалить">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                @endcan
            @else
                @can('restore-film', $film)
                    <form action="{{ route('films.restore', $film) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-success" title="Восстановить">
                            <i class="fa-solid fa-rotate-left me-1"></i>Восстановить
                        </button>
                    </form>
                @endcan
                @can('force-delete-film', $film)
                    <form action="{{ route('films.force-destroy', $film) }}" method="POST"
                          onsubmit="return confirm('Удалить «{{ $film->title }}» навсегда? Это необратимо.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" title="Удалить навсегда">
                            <i class="fa-solid fa-ban me-1"></i>Навсегда
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </article>
</div>
