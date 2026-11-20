<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/films');

Route::middleware('auth')->group(function () {

    // Breeze после входа/регистрации ведёт на route('dashboard')
    Route::get('/dashboard', fn () => redirect()->route('films.index'))->name('dashboard');

    // ---- Фильмы (CRUD) ----
    // withTrashed(['show']) — админ может открыть страницу мягко удалённого фильма
    Route::resource('films', FilmController::class)->withTrashed(['show']);
    Route::patch('films/{film}/restore', [FilmController::class, 'restore'])->withTrashed()->name('films.restore');
    Route::delete('films/{film}/force', [FilmController::class, 'forceDestroy'])->withTrashed()->name('films.force-destroy');

    // ---- Пользователи: ссылки по username, а не по ID ----
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user:username}', [UserController::class, 'show'])->name('users.show');

    // ---- Профиль (Breeze) ----
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// login, register, logout, forgot-password и т.д. (добавляет Breeze)
require __DIR__ . '/auth.php';
