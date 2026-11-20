<?php

namespace App\Providers;

use App\Models\Film;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1) создавать объекты может любой авторизованный пользователь
        Gate::define('create-film', fn (User $user) => true);

        // 2) редактировать — только владелец (и администратор)
        Gate::define('update-film', fn (User $user, Film $film) => $user->is_admin || $user->id === $film->user_id);

        // 3) мягко удалить — владелец (и администратор)
        Gate::define('delete-film', fn (User $user, Film $film) => $user->is_admin || $user->id === $film->user_id);

        // 4) восстановить и удалить навсегда — только администратор
        Gate::define('restore-film', fn (User $user, Film $film) => (bool) $user->is_admin);
        Gate::define('force-delete-film', fn (User $user, Film $film) => (bool) $user->is_admin);
    }
}
