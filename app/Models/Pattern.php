<?php

namespace App\Models;

use Database\Factories\PatternFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pattern extends Model
{
    /** @use HasFactory<PatternFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'instrument',
        'key',
        'tempo',
        'style',
        'difficulty',
        'content',
        'tablature',
        'notation_url',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jams(): BelongsToMany
    {
        return $this->belongsToMany(Jam::class)->withTimestamps();
    }
}
