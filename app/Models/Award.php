<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Award extends Model
{
    protected $fillable = ['film_id', 'award_year', 'award_name', 'category', 'is_winner'];

    protected $casts = [
        'is_winner' => 'boolean',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}
