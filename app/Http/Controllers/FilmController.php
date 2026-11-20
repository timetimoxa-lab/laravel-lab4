<?php

namespace App\Http\Controllers;

use App\Models\Film;
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

        return view('films.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create-film');

        $data = $request->validate($this->rules(posterRequired: true), $this->messages(), $this->attributeNames());
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

        return view('films.edit', compact('film'));
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        Gate::authorize('update-film', $film);

        $data = $request->validate($this->rules(posterRequired: false), $this->messages(), $this->attributeNames());
        $data = Arr::except($data, ['poster']);

        if ($request->hasFile('poster')) {
            Storage::disk('public')->delete($film->poster_path);
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

        Storage::disk('public')->delete($film->poster_path);
        $film->forceDelete();

        return back()->with('success', 'Фильм удалён навсегда.');
    }

    // ------------------------------------------------------------------

    private function rules(bool $posterRequired): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'original_title'    => ['nullable', 'string', 'max:255'],
            'genre'             => ['required', 'string', 'max:255'],
            'role'              => ['required', 'string', 'max:255'],
            'released_at'       => ['required', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
            'short_description' => ['required', 'string', 'max:500'],
            'description'       => ['required', 'string', 'max:5000'],
            'accent_color'      => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'poster'            => [$posterRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];
    }

    private function messages(): array
    {
        return [
            'required'                    => 'Поле «:attribute» обязательно для заполнения.',
            'max.string'                  => 'Поле «:attribute» не должно превышать :max символов.',
            'date'                        => 'Поле «:attribute» должно быть датой.',
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
            'accent_color'      => 'Цвет',
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
