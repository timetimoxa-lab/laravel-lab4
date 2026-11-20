<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FilmController extends Controller
{
    /** Все (не удалённые) фильмы всех пользователей */
    public function index(): View
    {
        $films = Film::with(['user', 'awards'])->orderBy('released_at')->get();

        return view('films.index', compact('films'));
    }

    public function create(): View
    {
        Gate::authorize('create-film');

        return view('films.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create-film');

        $this->sanitizeRequestText($request);

        $data = $request->validate(
            $this->rules(posterRequired: true),
            $this->messages(),
            $this->attributeNames()
        );

        $data = Arr::except($data, ['poster']);
        $data['poster_path'] = $this->storePoster($request->file('poster'));

        // через связь: в films.user_id записывается ID текущего пользователя
        $film = $request->user()->films()->create($data);

        return redirect()->route('films.show', $film)->with('success', 'Фильм добавлен.');
    }

    public function show(Film $film): View
    {
        // удалённые фильмы видит только администратор
        if ($film->trashed()) {
            Gate::authorize('restore-film', $film);
        }

        $film->load(['user', 'awards']);

        return view('films.show', compact('film'));
    }

    public function edit(Film $film): View
    {
        Gate::authorize('update-film', $film);

        return view('films.edit', array_merge(compact('film'), $this->formOptions($film->id)));
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        Gate::authorize('update-film', $film);

        $this->sanitizeRequestText($request);

        $data = $request->validate(
            $this->rules(posterRequired: false, ignoreId: $film->id),
            $this->messages(),
            $this->attributeNames()
        );

        $data = Arr::except($data, ['poster']);

        if ($request->hasFile('poster')) {
            if ($film->poster_path) {
                Storage::disk('public')->delete($film->poster_path);
            }
            $data['poster_path'] = $this->storePoster($request->file('poster'));
        }

        $film->update($data);

        return redirect()->route('films.show', $film)->with('success', 'Изменения сохранены.');
    }

    /** Мягкое удаление: владелец (или админ) */
    public function destroy(Film $film): RedirectResponse
    {
        Gate::authorize('delete-film', $film);

        $film->delete();

        return redirect()->route('films.index')->with('success', 'Фильм удалён (его может восстановить администратор).');
    }

    /** Восстановление мягко удалённого фильма: только админ */
    public function restore(Film $film): RedirectResponse
    {
        Gate::authorize('restore-film', $film);

        $film->restore();

        return back()->with('success', 'Фильм восстановлен.');
    }

    /** Удаление без возврата: только админ */
    public function forceDestroy(Film $film): RedirectResponse
    {
        Gate::authorize('force-delete-film', $film);

        if ($film->poster_path) {
            Storage::disk('public')->delete($film->poster_path);
        }
        $film->forceDelete();

        return back()->with('success', 'Фильм удалён навсегда.');
    }

    /**
     * AJAX-эндпоинт мгновенной проверки дубликатов и поиска похожих карточек.
     * Вызывается в моменте при вводе названия / оригинального названия.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $title = (string) $request->query('title', '');
        $originalTitle = (string) $request->query('original_title', '');
        $ignoreId = $request->query('ignore_id') ? (int) $request->query('ignore_id') : null;

        $normTitle = self::normalizeKey($title);
        $compactTitle = str_replace(' ', '', $normTitle);

        $normOrig = self::normalizeKey($originalTitle);
        $compactOrig = str_replace(' ', '', $normOrig);

        $films = Film::with('user:id,username')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->orderBy('title')
            ->get(['id', 'user_id', 'title', 'original_title', 'genre', 'role', 'released_at']);

        $exactTitle = null;
        $exactOriginal = null;
        $similar = [];

        foreach ($films as $film) {
            $fNormTitle = self::normalizeKey($film->title);
            $fCompactTitle = str_replace(' ', '', $fNormTitle);

            $fNormOrig = self::normalizeKey($film->original_title);
            $fCompactOrig = str_replace(' ', '', $fNormOrig);

            $payload = [
                'id'             => $film->id,
                'title'          => $film->title,
                'original_title' => $film->original_title,
                'genre'          => $film->genre,
                'role'           => $film->role,
                'year'           => $film->released_at ? $film->released_at->year : null,
                'owner'          => $film->user?->username,
                'url'            => route('films.show', $film),
            ];

            if ($normTitle !== '' && (
                $fNormTitle === $normTitle
                || ($compactTitle !== '' && $fCompactTitle === $compactTitle)
                || ($fNormOrig !== '' && $fNormOrig === $normTitle)
            )) {
                $exactTitle = $payload;
            }

            if ($normOrig !== '' && (
                ($fNormOrig !== '' && ($fNormOrig === $normOrig || ($compactOrig !== '' && $fCompactOrig === $compactOrig)))
                || $fNormTitle === $normOrig
            )) {
                $exactOriginal = $payload;
            }

            // Похожие карточки (подстрока или близкое совпадение) — отсортированы по алфавиту
            $isSimilar = false;
            if (mb_strlen($normTitle, 'UTF-8') >= 2) {
                if (str_contains($fNormTitle, $normTitle) || str_contains($normTitle, $fNormTitle)) {
                    $isSimilar = true;
                } elseif (mb_strlen($normTitle, 'UTF-8') >= 4 && self::isFuzzyMatch($normTitle, $fNormTitle)) {
                    $isSimilar = true;
                }
            }
            if (! $isSimilar && mb_strlen($normOrig, 'UTF-8') >= 2 && $fNormOrig !== '') {
                if (str_contains($fNormOrig, $normOrig) || str_contains($normOrig, $fNormOrig)) {
                    $isSimilar = true;
                }
            }

            if ($isSimilar) {
                $similar[] = $payload;
            }
        }

        usort($similar, fn (array $a, array $b) => mb_strtolower($a['title'], 'UTF-8') <=> mb_strtolower($b['title'], 'UTF-8'));

        return response()->json([
            'exact_title'    => $exactTitle,
            'exact_original' => $exactOriginal,
            'similar'        => array_slice($similar, 0, 8),
        ]);
    }

    // ------------------------------------------------------------------
    // Справочники и отсортированный индекс существующих карточек для формы
    // ------------------------------------------------------------------

    private function formOptions(?int $ignoreId = null): array
    {
        $defaultGenres = [
            'Биография',
            'Боевик, фантастика',
            'Детектив',
            'Драма',
            'Драма, мелодрама',
            'Комедия',
            'Криминал',
            'Криминальная драма',
            'Мелодрама',
            'Неонуар, детектив',
            'Приключения',
            'Психологический триллер',
            'Романтическая комедия',
            'Судебная драма',
            'Триллер',
            'Ужасы',
        ];

        $defaultRoles = [
            'Билли «Капитан Америка»',
            'Гаррет Бридлав',
            'Главная роль',
            'Джей Джей Гиттс',
            'Джек Торранс',
            'Джокер (Джек Напье)',
            'Мелвин Юдалл',
            'Полковник Натан Джессап',
            'Роль второго плана',
            'Рэндл Макмерфи',
            'Уоррен Шмидт',
            'Фрэнк Костелло',
            'Эпизодическая роль',
        ];

        $allFilms = Film::with('user:id,username')
            ->orderBy('title')
            ->get(['id', 'user_id', 'title', 'original_title', 'genre', 'role', 'released_at']);

        $dbGenres = $allFilms->pluck('genre')->filter()->unique()->all();
        $dbRoles  = $allFilms->pluck('role')->filter()->unique()->all();

        $genres = array_values(array_unique(array_merge($defaultGenres, $dbGenres)));
        $roles  = array_values(array_unique(array_merge($defaultRoles, $dbRoles)));

        usort($genres, fn (string $a, string $b) => mb_strtolower($a, 'UTF-8') <=> mb_strtolower($b, 'UTF-8'));
        usort($roles, fn (string $a, string $b) => mb_strtolower($a, 'UTF-8') <=> mb_strtolower($b, 'UTF-8'));

        // Предсортированный индекс всех карточек для 0ms-поиска дубликатов прямо при наборе
        $existingFilms = $allFilms
            ->reject(fn (Film $f) => $ignoreId !== null && $f->id === $ignoreId)
            ->map(fn (Film $f) => [
                'id'             => $f->id,
                'title'          => $f->title,
                'original_title' => $f->original_title,
                'genre'          => $f->genre,
                'role'           => $f->role,
                'year'           => $f->released_at ? $f->released_at->year : null,
                'owner'          => $f->user?->username,
                'url'            => route('films.show', $f),
                'norm_title'     => self::normalizeKey($f->title),
                'norm_orig'      => self::normalizeKey($f->original_title),
            ])
            ->sortBy(fn (array $item) => $item['norm_title'])
            ->values()
            ->all();

        return compact('genres', 'roles', 'existingFilms');
    }

    // ------------------------------------------------------------------
    // Нормализация и правила валидации (спецсимволы, лимиты, дубликаты)
    // ------------------------------------------------------------------

    /** Нормализация строки для надёжного выявления дубликатов (регистр, ё->е, пробелы, пунктуация) */
    public static function normalizeKey(?string $value): string
    {
        $text = mb_strtolower(trim((string) $value), 'UTF-8');
        $text = str_replace('ё', 'е', $text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    private static function isFuzzyMatch(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }
        $lenA = mb_strlen($a, 'UTF-8');
        $lenB = mb_strlen($b, 'UTF-8');
        if (abs($lenA - $lenB) > 2) {
            return false;
        }
        similar_text($a, $b, $percent);

        return $percent >= 82.0;
    }

    /** Схлопываем лишние пробелы в начале/конце перед валидацией */
    private function sanitizeRequestText(Request $request): void
    {
        $fields = ['title', 'original_title', 'genre', 'role', 'short_description', 'description'];
        $cleaned = [];

        foreach ($fields as $field) {
            if ($request->has($field) && is_string($request->input($field))) {
                $val = preg_replace('/[ \t]+/u', ' ', trim($request->input($field)));
                $cleaned[$field] = $val;
            }
        }

        if ($cleaned !== []) {
            $request->merge($cleaned);
        }
    }

    private function rules(bool $posterRequired, ?int $ignoreId = null): array
    {
        $safeText = $this->safeTextRule();

        return [
            'title' => [
                'required', 'string', 'min:2', 'max:150',
                $safeText,
                $this->uniqueNormalizedRule('title', $ignoreId),
            ],
            'original_title' => [
                'nullable', 'string', 'min:2', 'max:150',
                $safeText,
                $this->uniqueNormalizedRule('original_title', $ignoreId),
            ],
            'genre' => [
                'required', 'string', 'min:2', 'max:100', $safeText,
            ],
            'role' => [
                'required', 'string', 'min:2', 'max:120', $safeText,
            ],
            'released_at' => [
                'required', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:today',
            ],
            'short_description' => [
                'required', 'string', 'min:20', 'max:300',
                $safeText,
                $this->minWordsRule(4),
            ],
            'description' => [
                'required', 'string', 'min:50', 'max:3000',
                $safeText,
                $this->minWordsRule(10),
            ],
            'accent_color' => [
                'required', 'regex:/^#[0-9a-fA-F]{6}$/',
            ],
            'poster' => [
                $posterRequired ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096',
            ],
        ];
    }

    /**
     * Серверная защита от спецсимволов:
     * 1) разрешает @, #, $, %, & и базовую пунктуацию внутри текста, но запрещает
     *    опасные технические символы (<, >, \, /, ^, *, _, =, +, ~, |, [, ], {, });
     * 2) запрещает ввод только из спецсимволов / 1 символа (нужно >= 2 букв или цифр);
     * 3) запрещает мусорные повторы спецсимволов подряд ("!!", "??", "--", "@@", "##" и т.д.).
     */
    private function safeTextRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $text = trim((string) $value);
            if ($text === '') {
                return;
            }

            if (preg_match('/[^\p{L}\p{N}\s\-\.,:;«»"\'()!?—–@#$%&]/u', $text)) {
                $fail('Поле содержит запрещённые технические символы (<, >, \\, /, ^, *, _, =, +, ~, |, [, ], {, }).');
                return;
            }

            if (preg_match('/([!?,\-:;@#$%&])\1{1,}/u', $text)) {
                $fail('Не допускаются повторяющиеся подряд спецсимволы (например, «!!», «??», «--», «@@», «##»).');
                return;
            }

            $alnumCount = preg_match_all('/[\p{L}\p{N}]/u', $text);
            if ($alnumCount < 2) {
                $fail('Спецсимволы не могут быть единственным содержимым поля — введите минимум 2 буквы или цифры.');
            }
        };
    }

    /** Серверная защита от дубликатов (с учётом регистра, ё/е, пробелов и пунктуации) */
    private function uniqueNormalizedRule(string $column, ?int $ignoreId = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($column, $ignoreId): void {
            $norm = self::normalizeKey((string) $value);
            if ($norm === '') {
                return;
            }
            $compact = str_replace(' ', '', $norm);

            $existing = Film::with('user:id,username')
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->get(['id', 'user_id', 'title', 'original_title', 'released_at']);

            foreach ($existing as $film) {
                $candidateTitle = self::normalizeKey($film->title);
                $candidateOrig  = self::normalizeKey($film->original_title);

                $match = false;
                if ($column === 'title') {
                    $match = ($candidateTitle === $norm)
                        || (str_replace(' ', '', $candidateTitle) === $compact)
                        || ($candidateOrig !== '' && $candidateOrig === $norm);
                } else {
                    $match = ($candidateOrig !== '' && ($candidateOrig === $norm || str_replace(' ', '', $candidateOrig) === $compact))
                        || ($candidateTitle === $norm);
                }

                if ($match) {
                    $year = $film->released_at ? " ({$film->released_at->year})" : '';
                    $owner = $film->user ? " (добавил @{$film->user->username})" : '';
                    $fail("Дубликат: фильм «{$film->title}»{$year}{$owner} уже существует в базе.");
                    return;
                }
            }
        };
    }

    /** Проверка минимального количества слов (кириллица, латиница, числа) */
    private function minWordsRule(int $minWords): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($minWords): void {
            $text = trim((string) $value);
            if ($text === '') {
                return;
            }

            $wordCount = preg_match_all('/[\p{L}\p{N}]+/u', $text);
            if ($wordCount < $minWords) {
                $fail("Поле должно содержать не менее {$minWords} слов (сейчас: {$wordCount}).");
            }
        };
    }

    private function messages(): array
    {
        return [
            'required'                    => 'Поле «:attribute» обязательно для заполнения.',
            'min.string'                  => 'Поле «:attribute» должно содержать не менее :min символов.',
            'max.string'                  => 'Поле «:attribute» не должно превышать :max символов.',
            'date'                        => 'Поле «:attribute» должно быть корректной датой.',
            'released_at.after_or_equal'  => 'Дата не может быть раньше 01.01.1900.',
            'released_at.before_or_equal' => 'Дата не может быть в будущем.',
            'accent_color.regex'          => 'Цвет должен быть в формате #RRGGBB.',
            'poster.image'                => 'Файл должен быть изображением.',
            'poster.mimes'                => 'Допустимые форматы постера: jpg, jpeg, png.',
            'poster.max'                  => 'Размер постера не должен превышать 4 МБ.',
        ];
    }

    private function attributeNames(): array
    {
        return [
            'title'             => 'Название',
            'original_title'    => 'Оригинальное название',
            'genre'             => 'Жанр',
            'role'              => 'Роль',
            'released_at'       => 'Дата выхода',
            'short_description' => 'Краткое описание',
            'description'       => 'Описание',
            'accent_color'      => 'Цвет карточки',
            'poster'            => 'Постер',
        ];
    }

    private function storePoster(UploadedFile $file): string
    {
        $image = (new ImageManager(new Driver()))
            ->read($file->getRealPath())
            ->cover(400, 600);

        $path = 'posters/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, (string) $image->toJpeg(85));

        return $path;
    }
}
