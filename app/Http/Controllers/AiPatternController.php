<?php

namespace App\Http\Controllers;

use App\Services\PatternGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiPatternController extends Controller
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

    public function create(Request $request): View
    {
        return view('patterns.generate', [
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    public function store(Request $request, PatternGenerationService $service): View
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'max:100'],
            'instrument' => ['nullable', 'string', 'max:100'],
            'key' => ['nullable', 'string', 'max:50'],
            'tempo' => ['nullable', 'integer', 'min:20', 'max:300'],
            'style' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['nullable', 'in:beginner,intermediate,advanced'],
        ]);

        $generated = $service->generate($validated);

        return view('patterns.generated-preview', [
            'generated' => $generated,
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
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

        return redirect()->route('patterns.index')->with('status', 'Generated pattern saved.');
    }
}
