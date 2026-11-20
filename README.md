# ЛР 4. Аутентификация: Laravel Breeze, Gates, Soft Deletes

Файлы для наложения на проект из ЛР 3 (`D:\lab3`). Нужен PHP 8.2+ (у вас 8.3 в D:\php83).
В терминале перед работой: `$env:Path = "D:\php83;" + $env:Path`

## Порядок (с коммитами)

### 0. Зафиксируйте ЛР 3
    git add .
    git commit -m "ЛР 3: готово"

### 1. Установка Breeze (коммит)
    composer require laravel/breeze --dev
    php artisan breeze:install blade
Ответы: dark mode — No, testing framework — PHPUnit.
В конце Breeze попытается выполнить `npm run build` и выдаст ошибку (у нас Mix, не Vite) — это нормально.

Breeze добавил Vite/Tailwind. Удалите лишние файлы:
`vite.config.js`, `tailwind.config.js`, `postcss.config.js`, `resources\css\app.css`, `resources\js\bootstrap.js`

    git add .
    git commit -m "ЛР 4: установлен Laravel Breeze"

### 2. Наложение файлов (коммит)
Скопируйте всё из архива в `D:\lab3` с заменой (вернутся Mix/Bootstrap-версии package.json, app.js, layout, routes/web.php).

    npm install
    npm run dev
    php artisan migrate:fresh --seed
    php artisan storage:link
    php artisan serve

    git add .
    git commit -m "ЛР 4: user_id, is_admin, username, Gates, Soft Deletes"

### 3. push / pull / архив
    git remote add origin https://github.com/<логин>/<репозиторий>.git
    git branch -M main
    git push -u origin main
    git pull        # после правки любого файла на GitHub
    git archive -o archive.zip HEAD

## Демо-пользователи (пароль: password)
| username | роль |
|---|---|
| admin | администратор (is_admin = true), без фильмов |
| ivan | Китайский квартал, Пролетая над гнездом кукушки, Лучше не бывает |
| maria | Условия нежности, Несколько хороших парней |

Вход: http://127.0.0.1:8000/login

## Флаг администратора через tinker (по заданию)
    php artisan tinker
    >>> $u = App\Models\User::where('username', 'ivan')->first();
    >>> $u->is_admin = true; $u->save();
(`update(['is_admin' => true])` не сработает: поле не в $fillable.)

## Где что реализовано
| Требование | Файл |
|---|---|
| user_id в films | database/migrations/2026_11_20_100000_add_user_id_to_films_table.php |
| is_admin (default false) и username в users | database/migrations/2026_11_20_100100_add_is_admin_and_username_to_users_table.php |
| Breeze: /login, /register | routes/auth.php (Breeze), resources/views/auth/* (Bootstrap) |
| Регистрация с username | app/Http/Controllers/Auth/RegisteredUserController.php |
| Список объектов пользователя по username | routes/web.php (`users/{user:username}`), UserController::show |
| Список всех пользователей, навигация | users/index.blade.php, users/show.blade.php |
| user_id записывается при создании | FilmController::store (`$request->user()->films()->create`) |
| Gates | app/Providers/AppServiceProvider.php |
| Проверка в контроллере | FilmController (`Gate::authorize`) |
| Проверка в модели (Events/Closures) | app/Models/Film.php, метод booted() |
| Проверка в интерфейсе (@can) | films/_card.blade.php, films/show.blade.php |
| Soft Deletes, восстановить, удалить навсегда | FilmController::destroy / restore / forceDestroy |

## Что показать на демонстрации
1. Регистрация нового пользователя (username обязателен), вход, выход.
2. /users, /users/ivan, /users/maria — списки по username (не по ID).
3. Обычный пользователь видит кнопки правки/удаления только у своих фильмов; чужой /films/ID/edit даёт 403.
4. Новый фильм записывается на текущего пользователя (поле user_id в БД).
5. Мягкое удаление своего фильма; под admin: в /users/ivan виден удалённый фильм, кнопки «Восстановить» и «Навсегда».
