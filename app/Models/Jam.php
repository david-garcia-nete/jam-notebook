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

    public const SECTIONS = [
        'Intro',
        'Verse',
        'Pre-Chorus',
        'Chorus',
        'Interlude',
        'Bridge',
        'Solo',
        'Outro',
    ];

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

    public static function sectionOrderSql(string $column = 'jam_pattern.section'): string
    {
        $cases = collect(self::SECTIONS)
            ->map(fn ($section, $index) => "WHEN ? THEN {$index}")
            ->join(' ');

        return "CASE {$column} {$cases} ELSE ".count(self::SECTIONS).' END';
    }

    public static function normalizeSection(string $section): string
    {
        $normalizedInput = static::normalizeSectionKey($section);

        foreach (self::SECTIONS as $canonicalSection) {
            if (static::normalizeSectionKey($canonicalSection) === $normalizedInput) {
                return $canonicalSection;
            }
        }

        return collect(explode('-', $normalizedInput))
            ->filter()
            ->map(fn (string $part) => ucfirst($part))
            ->join('-');
    }

    private static function normalizeSectionKey(string $section): string
    {
        return str($section)
            ->trim()
            ->replace('_', '-')
            ->replace(' ', '-')
            ->lower()
            ->replaceMatches('/-+/', '-')
            ->toString();
    }
}
