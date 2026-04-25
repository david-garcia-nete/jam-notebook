<?php

namespace App\Models;

use Database\Factories\PatternFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
