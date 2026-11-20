<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Список всех пользователей (навигация по ним) */
    public function index(): View
    {
        $users = User::withCount('films')->orderBy('username')->get();

        return view('users.index', compact('users'));
    }

    /** Объекты пользователя по его username: /users/{username} */
    public function show(Request $request, User $user): View
    {
        $filmsQuery = $user->films()->with(['user', 'awards'])->orderBy('released_at');

        // администратор видит и мягко удалённые объекты
        if ($request->user()->is_admin) {
            $filmsQuery->withTrashed();
        }

        $films = $filmsQuery->get();
        $allUsers = User::orderBy('username')->get(['id', 'name', 'username']);

        return view('users.show', compact('user', 'films', 'allUsers'));
    }
}
