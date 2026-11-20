<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Film extends Model
{
    use SoftDeletes;

    // user_id не в $fillable: владелец берётся из Auth, а не из формы
    protected $fillable = [
        'title',
        'original_title',
        'genre',
        'role',
        'released_at',
        'short_description',
        'description',
        'poster_path',
        'accent_color',
    ];

    // ------------------------------------------------------------------
    // Проверка прав на уровне МОДЕЛИ (Eloquent Events / Closures).
    // Возврат false из события отменяет операцию.
    // Из консоли без авторизованного пользователя (сидеры, tinker) всё разрешено.
    // ------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function (Film $film) {
            if (! $film->user_id && Auth::check()) {
                $film->user_id = Auth::id();               // кто создал — тот владелец
            }
            if (! $film->user_id && ! app()->runningInConsole()) {
                return false;                              // объект не существует без пользователя
            }
        });

        static::updating(fn (Film $film) => self::actorMayManage($film));

        static::deleting(function (Film $film) {
            // полное удаление — только админ, мягкое — владелец или админ
            return $film->isForceDeleting()
                ? self::actorIsAdmin()
                : self::actorMayManage($film);
        });

        static::restoring(fn (Film $film) => self::actorIsAdmin());   // восстановление — только админ
    }

    private static function actorIsAdmin(): bool
    {
        return Auth::check() ? (bool) Auth::user()->is_admin : app()->runningInConsole();
    }

    private static function actorMayManage(Film $film): bool
    {
        if (! Auth::check()) {
            return app()->runningInConsole();
        }

        return Auth::user()->is_admin || Auth::id() === $film->user_id;
    }

    // ---------- Связи ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class)->orderBy('award_year');
    }

    // ---------- Мутаторы / аксессоры ----------

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => trim(preg_replace('/\s+/u', ' ', (string) $value)),
        );
    }

    protected function releasedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function releaseYear(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => Carbon::parse($attributes['released_at'])->year,
        );
    }

    protected function posterUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => asset('storage/' . $attributes['poster_path']),
        );
    }

    protected function accentTextColor(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $hex = ltrim($attributes['accent_color'] ?? '#3b4cca', '#');
                [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
                $luma = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

                return $luma > 0.6 ? '#212529' : '#ffffff';
            },
        );
    }
}
