<?php

namespace App\Http\Controllers;

use App\Models\Jam;
use App\Models\Pattern;
use App\Services\PatternGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiJamDevelopmentController extends Controller
{
    private const ALLOWED_SUGGESTION_TYPES = [
        'new_section',
        'new_pattern',
        'transition',
    ];

    public function create(Jam $jam): View
    {
        $this->ensureOwner($jam);

        $jam->load(['patterns' => fn ($query) => $query
            ->where('user_id', auth()->id())
            ->orderByRaw(
                "CASE jam_pattern.section WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 WHEN ? THEN 5 WHEN ? THEN 6 ELSE 7 END",
                Jam::SECTIONS
            )
            ->orderBy('jam_pattern.section')
            ->orderBy('jam_pattern.position')]);

        return view('jams.develop', [
            'jam' => $jam,
        ]);
    }

    public function store(Request $request, Jam $jam, PatternGenerationService $service): View
    {
        $this->ensureOwner($jam);

        $validated = $request->validate([
            'instruction' => ['nullable', 'string', 'max:2000'],
        ]);

        $jam->load(['patterns' => fn ($query) => $query
            ->where('user_id', auth()->id())
            ->orderByRaw(
                "CASE jam_pattern.section WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 WHEN ? THEN 5 WHEN ? THEN 6 ELSE 7 END",
                Jam::SECTIONS
            )
            ->orderBy('jam_pattern.section')
            ->orderBy('jam_pattern.position')]);

        $suggestions = $service->developJam($jam, $validated['instruction'] ?? null);

        return view('jams.develop-preview', [
            'jam' => $jam,
            'suggestions' => $suggestions,
            'instruction' => $validated['instruction'] ?? null,
        ]);
    }

    public function save(Request $request, Jam $jam): RedirectResponse
    {
        $this->ensureOwner($jam);

        $validated = $request->validate([
            'suggestions_json' => ['required', 'string'],
            'selected' => ['array'],
            'selected.*' => ['integer', 'min:0'],
            'attach_to_jam' => ['nullable', 'boolean'],
        ]);

        $payload = json_decode($validated['suggestions_json'], true);
        abort_if(! is_array($payload), 422);

        $allSuggestions = $payload['suggestions'] ?? [];
        abort_if(! is_array($allSuggestions), 422);

        $selectedIndexes = collect($validated['selected'] ?? [])->map(fn ($index) => (int) $index)->unique()->values();
        $attachToJam = (bool) ($validated['attach_to_jam'] ?? true);

        foreach ($selectedIndexes as $index) {
            $suggestion = $allSuggestions[$index] ?? null;

            if (! is_array($suggestion)) {
                continue;
            }

            $type = trim((string) ($suggestion['type'] ?? ''));

            if (! in_array($type, self::ALLOWED_SUGGESTION_TYPES, true)) {
                continue;
            }

            if ($type === 'new_section') {
                $section = Jam::normalizeSection((string) ($suggestion['section'] ?? 'Bridge'));
                $description = trim((string) ($suggestion['description'] ?? ''));

                if ($description === '') {
                    $description = 'Create a new '.$section.' section idea for this jam.';
                }

                $pattern = $this->createOrReusePattern($request, [
                    'title' => $section.' Section Idea',
                    'type' => 'arrangement idea',
                    'instrument' => $this->nullableString($suggestion['instrument'] ?? null),
                    'content' => $description,
                    'notes' => 'AI suggested adding a '.$section.' section.',
                ]);

                if (! $pattern) {
                    continue;
                }

                if ($attachToJam) {
                    $this->attachPatternToSection($jam, $pattern->id, $section);
                }

                continue;
            }

            if ($type === 'new_pattern') {
                $section = Jam::normalizeSection((string) ($suggestion['section'] ?? 'Verse'));

                $pattern = $this->createOrReusePattern($request, [
                    'title' => trim((string) ($suggestion['title'] ?? 'AI Jam Idea')),
                    'instrument' => $this->nullableString($suggestion['instrument'] ?? null),
                    'content' => trim((string) ($suggestion['content'] ?? '')),
                    'notes' => $this->nullableString($suggestion['notes'] ?? null),
                ]);

                if (! $pattern) {
                    continue;
                }

                if ($attachToJam) {
                    $this->attachPatternToSection($jam, $pattern->id, $section);
                }

                continue;
            }

            if ($type === 'transition') {
                $from = Jam::normalizeSection((string) ($suggestion['from_section'] ?? 'Verse'));
                $to = Jam::normalizeSection((string) ($suggestion['to_section'] ?? 'Chorus'));
                $description = trim((string) ($suggestion['description'] ?? ''));

                if ($description === '') {
                    continue;
                }

                $pattern = $this->createOrReusePattern($request, [
                    'title' => 'Transition: '.$from.' → '.$to,
                    'type' => 'arrangement idea',
                    'content' => $description,
                    'notes' => 'From '.$from.' to '.$to,
                ]);

                if ($pattern && $attachToJam) {
                    $this->attachPatternToSection($jam, $pattern->id, $to);
                }
            }
        }

        return redirect()->route('jams.show', $jam)->with('status', 'AI jam suggestions saved.');
    }

    private function attachPatternToSection(Jam $jam, int $patternId, string $section): void
    {
        $alreadyAttached = DB::table('jam_pattern')
            ->where('jam_id', $jam->id)
            ->where('pattern_id', $patternId)
            ->exists();

        if ($alreadyAttached) {
            return;
        }

        $nextPosition = (int) DB::table('jam_pattern')
            ->where('jam_id', $jam->id)
            ->where('section', $section)
            ->max('position') + 1;

        $jam->patterns()->attach($patternId, [
            'section' => $section,
            'position' => $nextPosition,
        ]);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function createOrReusePattern(Request $request, array $attributes): ?Pattern
    {
        $title = trim((string) ($attributes['title'] ?? ''));
        $content = trim((string) ($attributes['content'] ?? ''));

        if ($content === '') {
            return null;
        }

        $attributes['title'] = $title === '' ? 'AI Jam Idea' : $title;
        $attributes['content'] = $content;

        $existingPattern = $this->findExistingUserPattern($request, $attributes);

        if ($existingPattern) {
            return $existingPattern;
        }

        return $request->user()->patterns()->create($attributes);
    }

    private function findExistingUserPattern(Request $request, array $attributes): ?Pattern
    {
        $normalized = [
            'title' => trim((string) ($attributes['title'] ?? '')),
            'type' => $this->nullableString($attributes['type'] ?? null),
            'instrument' => $this->nullableString($attributes['instrument'] ?? null),
            'key' => $this->nullableString($attributes['key'] ?? null),
            'tempo' => isset($attributes['tempo']) ? (int) $attributes['tempo'] : null,
            'style' => $this->nullableString($attributes['style'] ?? null),
            'difficulty' => $this->nullableString($attributes['difficulty'] ?? null),
            'content' => trim((string) ($attributes['content'] ?? '')),
            'notes' => $this->nullableString($attributes['notes'] ?? null),
        ];

        if ($normalized['title'] === '' || $normalized['content'] === '') {
            return null;
        }

        $query = $request->user()->patterns();

        foreach (['title', 'type', 'instrument', 'key', 'style', 'difficulty', 'content', 'notes'] as $field) {
            if ($normalized[$field] === null) {
                $query->whereNull($field);

                continue;
            }

            $query->whereRaw('LOWER(TRIM('.$field.')) = ?', [strtolower($normalized[$field])]);
        }

        if ($normalized['tempo'] === null) {
            $query->whereNull('tempo');
        } else {
            $query->where('tempo', $normalized['tempo']);
        }

        return $query->first();
    }

    private function ensureOwner(Jam $jam): void
    {
        abort_if($jam->user_id !== auth()->id(), 403);
    }
}
