<?php

namespace App\Http\Controllers;

use App\Models\Pattern;
use App\Services\PatternGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiPatternDevelopmentController extends Controller
{
    private const TYPES = [
        'chord progression',
        'bassline',
        'drum groove',
        'melody',
        'lyrics',
        'exercise',
        'arrangement idea',
    ];

    private const INSTRUMENTS = [
        'piano',
        'guitar',
        'bass',
        'drums',
        'vocals',
        'synth',
        'other',
    ];

    private const DIFFICULTIES = [
        'beginner',
        'intermediate',
        'advanced',
    ];

    public function create(Pattern $pattern): View
    {
        $this->ensureOwner($pattern);

        return view('patterns.develop', [
            'pattern' => $pattern,
        ]);
    }

    public function store(Request $request, Pattern $pattern, PatternGenerationService $service): View
    {
        $this->ensureOwner($pattern);

        $validated = $request->validate([
            'instruction' => ['nullable', 'string', 'max:2000'],
        ]);

        $generated = $service->develop($pattern, $validated['instruction'] ?? null);

        return view('patterns.developed-preview', [
            'pattern' => $pattern,
            'generated' => $generated,
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    public function save(Request $request, Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($pattern);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'instrument' => ['nullable', 'string', 'max:100'],
            'key' => ['nullable', 'string', 'max:50'],
            'tempo' => ['nullable', 'integer', 'min:20', 'max:300'],
            'style' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['nullable', 'in:beginner,intermediate,advanced'],
            'content' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $request->user()->patterns()->create($validated);

        return redirect()->route('patterns.index')->with('status', 'Developed pattern saved.');
    }

    private function ensureOwner(Pattern $pattern): void
    {
        abort_if($pattern->user_id !== auth()->id(), 403);
    }
}
