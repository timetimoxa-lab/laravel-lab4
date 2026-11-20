<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FilmSeeder extends Seeder
{
    public function run(): void
    {
        $films = [
            [
                'slug' => 'chinatown',
                'owner' => 'ivan',
                'title' => 'Китайский квартал',
                'original_title' => 'Chinatown',
                'genre' => 'Неонуар, детектив',
                'role' => 'Джей Джей Гиттс',
                'released_at' => '1974-06-20',
                'accent_color' => '#c9a227',
                'short_description' => 'Лос-Анджелес 1930-х: частный детектив берётся за обычное дело об измене и попадает в паутину коррупции.',
                'description' => 'Николсон играет частного детектива Гиттса. Фильм Романа Полански стал эталоном неонуара. За эту роль актёр получил «Золотой глобус» и был номинирован на «Оскар».',
                'awards' => [
                    [1975, 'Оскар', 'Лучшая мужская роль', false],
                    [1975, 'Золотой глобус', 'Лучшая мужская роль (драма)', true],
                ],
            ],
            [
                'slug' => 'cuckoo',
                'owner' => 'ivan',
                'title' => 'Пролетая над гнездом кукушки',
                'original_title' => "One Flew Over the Cuckoo's Nest",
                'genre' => 'Драма',
                'role' => 'Рэндл Макмерфи',
                'released_at' => '1975-11-19',
                'accent_color' => '#2f8f83',
                'short_description' => 'Бунтарь Макмерфи попадает в психиатрическую клинику и бросает вызов её жёстким порядкам.',
                'description' => 'Режиссёр фильма — Милош Форман. Картина получила «большую пятёрку» «Оскаров» (фильм, режиссёр, актёр, актриса, сценарий), а Николсон впервые завоевал «Оскар» за главную роль и «Золотой глобус».',
                'awards' => [
                    [1976, 'Оскар', 'Лучшая мужская роль', true],
                    [1976, 'Золотой глобус', 'Лучшая мужская роль (драма)', true],
                ],
            ],
            [
                'slug' => 'terms',
                'owner' => 'maria',
                'title' => 'Условия нежности',
                'original_title' => 'Terms of Endearment',
                'genre' => 'Драма, мелодрама',
                'role' => 'Гаррет Бридлав',
                'released_at' => '1983-11-23',
                'accent_color' => '#c0577a',
                'short_description' => 'История матери и дочери, в которую вносит тепло и иронию отставной астронавт — сосед героини.',
                'description' => 'Николсон сыграл бывшего астронавта Бридлава — небольшую, но яркую роль. Она принесла ему «Оскар» как актёру второго плана и «Золотой глобус». Сам фильм получил «Оскар» как лучший.',
                'awards' => [
                    [1984, 'Оскар', 'Лучшая мужская роль второго плана', true],
                    [1984, 'Золотой глобус', 'Лучшая мужская роль второго плана', true],
                ],
            ],
            [
                'slug' => 'fewgood',
                'owner' => 'maria',
                'title' => 'Несколько хороших парней',
                'original_title' => 'A Few Good Men',
                'genre' => 'Судебная драма',
                'role' => 'Полковник Натан Джессап',
                'released_at' => '1992-12-11',
                'accent_color' => '#3d6fb8',
                'short_description' => 'Двое молодых морпехов обвиняются в убийстве, и защитнику приходится столкнуться с высшим офицерским составом.',
                'description' => 'Николсон сыграл полковника Джессапа — героя всего нескольких сцен, но ключевых для картины. Его сцена допроса в суде — одна из самых известных в кино. Роль принесла номинации на «Оскар» и «Золотой глобус».',
                'awards' => [
                    [1993, 'Оскар', 'Лучшая мужская роль второго плана', false],
                    [1993, 'Золотой глобус', 'Лучшая мужская роль второго плана', false],
                ],
            ],
            [
                'slug' => 'asgood',
                'owner' => 'ivan',
                'title' => 'Лучше не бывает',
                'original_title' => 'As Good as It Gets',
                'genre' => 'Романтическая комедия',
                'role' => 'Мелвин Юдалл',
                'released_at' => '1997-12-25',
                'accent_color' => '#d9742b',
                'short_description' => 'Желчный писатель с навязчивыми привычками неожиданно меняется под влиянием официантки и соседа-художника.',
                'description' => 'Николсон сыграл Мелвина Юдалла, писателя-мизантропа. Третий «Оскар» в карьере и «Золотой глобус» за лучшую мужскую роль в комедии подтвердили статус актёра.',
                'awards' => [
                    [1998, 'Оскар', 'Лучшая мужская роль', true],
                    [1998, 'Золотой глобус', 'Лучшая мужская роль (комедия или мюзикл)', true],
                ],
            ],
        ];

        $users = User::pluck('id', 'username');   // ['ivan' => 2, 'maria' => 3, ...]
        $n = count($films);

        foreach ($films as $i => $item) {
            // Постеры лежат в репозитории (database/seeders/posters),
            // а храниться должны в storage/app/public/posters
            $posterPath = 'posters/' . $item['slug'] . '.jpg';
            Storage::disk('public')->put(
                $posterPath,
                file_get_contents(database_path('seeders/posters/' . $item['slug'] . '.jpg'))
            );

            $film = new Film([
                'title' => $item['title'],
                'original_title' => $item['original_title'],
                'genre' => $item['genre'],
                'role' => $item['role'],
                'released_at' => $item['released_at'],
                'short_description' => $item['short_description'],
                'description' => $item['description'],
                'poster_path' => $posterPath,
                'accent_color' => $item['accent_color'],
            ]);
            $film->user_id = $users[$item['owner']];                 // владелец фильма
            $film->created_at = now()->subDays($n - $i);              // разные даты для ленты
            $film->save();

            foreach ($item['awards'] as [$year, $name, $category, $isWinner]) {
                $film->awards()->create([
                    'award_year' => $year,
                    'award_name' => $name,
                    'category' => $category,
                    'is_winner' => $isWinner,
                ]);
            }
        }
    }
}
