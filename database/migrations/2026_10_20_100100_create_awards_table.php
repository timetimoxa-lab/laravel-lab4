<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('award_year');    // год церемонии
            $table->string('award_name');                  // Оскар, Золотой глобус
            $table->string('category');                    // номинация
            $table->boolean('is_winner')->default(false);  // победа / номинация
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
