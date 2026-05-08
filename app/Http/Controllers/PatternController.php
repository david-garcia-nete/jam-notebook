<?php

namespace App\Http\Controllers;

use App\Models\Pattern;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatternController extends Controller
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

    public function index(Request $request): View
    {
        $user = $request->user();

        $patterns = Pattern::query()
            ->where('user_id', $user->id)
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('instrument'), fn ($query) => $query->where('instrument', $request->string('instrument')))
            ->when($request->filled('style'), fn ($query) => $query->where('style', $request->string('style')))
            ->latest()
            ->get();

        return view('patterns.index', [
            'patterns' => $patterns,
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'filters' => $request->only(['type', 'instrument', 'style']),
        ]);
    }

    public function create(): View
    {
        return view('patterns.create', [
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePattern($request);

        $request->user()->patterns()->create($validated);

        return redirect()->route('patterns.index')->with('status', 'Pattern saved.');
    }

    public function edit(Pattern $pattern): View
    {
        $this->ensureOwner($pattern);

        return view('patterns.edit', [
            'pattern' => $pattern,
            'types' => self::TYPES,
            'instruments' => self::INSTRUMENTS,
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    public function show(Pattern $pattern): View
    {
        $this->ensureOwner($pattern);

        return view('patterns.show', [
            'pattern' => $pattern,
        ]);
    }

    public function update(Request $request, Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($pattern);

        $pattern->update($this->validatePattern($request));

        return redirect()->route('patterns.index')->with('status', 'Pattern updated.');
    }

    public function destroy(Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($pattern);

        $pattern->delete();

        return redirect()->route('patterns.index')->with('status', 'Pattern deleted.');
    }

    private function ensureOwner(Pattern $pattern): void
    {
        abort_if($pattern->user_id !== auth()->id(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePattern(Request $request): array
    {
        return $request->validate([
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
    }
}
