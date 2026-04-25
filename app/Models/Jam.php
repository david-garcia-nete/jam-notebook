<?php

namespace App\Models;

use Database\Factories\JamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Jam extends Model
{
    /** @use HasFactory<JamFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'key',
        'tempo',
        'style',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(Pattern::class)
            ->withPivot(['section', 'position', 'notes'])
            ->withTimestamps();
    }
}
