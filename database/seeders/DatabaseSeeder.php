<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Пароль у всех демо-пользователей: password
        $admin = User::create(['name' => 'Администратор', 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $admin->forceFill(['is_admin' => true])->save();   // is_admin не fillable -> forceFill

        User::create(['name' => 'Иван Петров',    'username' => 'ivan',  'email' => 'ivan@example.com',  'password' => 'password']);
        User::create(['name' => 'Мария Смирнова', 'username' => 'maria', 'email' => 'maria@example.com', 'password' => 'password']);

        $this->call(FilmSeeder::class);   // фильмы привязываются к пользователям
    }
}
