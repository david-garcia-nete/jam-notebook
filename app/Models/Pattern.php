<?php

namespace App\Models;

use Database\Factories\PatternFactory;
use App\Services\PatternEmbedSanitizer;
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
        'notation_url',
        'embed_code',
        'notes',
    ];

    public function sanitizedEmbedCode(): ?string
    {
        return PatternEmbedSanitizer::sanitize($this->embed_code);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jams(): BelongsToMany
    {
        return $this->belongsToMany(Jam::class)->withTimestamps();
    }
}
