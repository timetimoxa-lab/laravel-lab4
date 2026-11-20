<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('title');                       // название
            $table->string('original_title')->nullable();  // оригинальное название
            $table->string('genre');                       // жанр
            $table->string('role');                        // роль Николсона
            $table->date('released_at');                   // дата выхода
            $table->text('short_description');
            $table->text('description');
            $table->string('poster_path');                 // путь в storage/app/public
            $table->string('accent_color', 7)->default('#3b4cca');
            $table->timestamps();
            $table->softDeletes();                         // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
