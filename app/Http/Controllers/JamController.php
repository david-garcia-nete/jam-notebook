<?php

namespace App\Http\Controllers;

use App\Models\Jam;
use App\Models\Pattern;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JamController extends Controller
{
    public function index(Request $request): View
    {
        $jams = Jam::query()
            ->where('user_id', $request->user()->id)
            ->withCount('patterns')
            ->latest()
            ->get();

        return view('jams.index', [
            'jams' => $jams,
        ]);
    }

    public function create(): View
    {
        return view('jams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJam($request);

        $request->user()->jams()->create($validated);

        return redirect()->route('jams.index')->with('status', 'Jam created.');
    }

    public function show(Jam $jam): View
    {
        $this->ensureOwner($jam);

        $jam->load(['patterns' => fn ($query) => $query
            ->where('user_id', auth()->id())
            ->orderByRaw(Jam::sectionOrderSql(), Jam::SECTIONS)
            ->orderBy('jam_pattern.section')
            ->orderBy('jam_pattern.position')
            ->orderBy('patterns.created_at', 'desc')]);

        $availablePatterns = Pattern::query()
            ->where('user_id', auth()->id())
            ->whereNotIn('id', $jam->patterns->pluck('id'))
            ->latest()
            ->get();

        return view('jams.show', [
            'jam' => $jam,
            'availablePatterns' => $availablePatterns,
        ]);
    }

    public function edit(Jam $jam): View
    {
        $this->ensureOwner($jam);

        return view('jams.edit', [
            'jam' => $jam,
        ]);
    }

    public function update(Request $request, Jam $jam): RedirectResponse
    {
        $this->ensureOwner($jam);

        $jam->update($this->validateJam($request));

        return redirect()->route('jams.show', $jam)->with('status', 'Jam updated.');
    }

    public function destroy(Jam $jam): RedirectResponse
    {
        $this->ensureOwner($jam);

        $jam->delete();

        return redirect()->route('jams.index')->with('status', 'Jam deleted.');
    }

    public function attachPattern(Request $request, Jam $jam): RedirectResponse
    {
        $this->ensureOwner($jam);

        $validated = $request->validate([
            'pattern_id' => ['required', 'integer', 'exists:patterns,id'],
            'section' => ['required', 'string', 'max:50'],
        ]);

        $pattern = Pattern::query()->findOrFail($validated['pattern_id']);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $section = Jam::normalizeSection($validated['section']);

        $alreadyAttached = $jam->patterns()->where('patterns.id', $pattern->id)->exists();

        if (! $alreadyAttached) {
            $nextPosition = (int) DB::table('jam_pattern')
                ->where('jam_id', $jam->id)
                ->where('section', $section)
                ->max('position') + 1;

            $jam->patterns()->attach($pattern->id, [
                'section' => $section,
                'position' => $nextPosition,
            ]);
        }

        return redirect()->route('jams.show', $jam)->with('status', 'Pattern added to jam.');
    }

    public function updatePatternPlacement(Request $request, Jam $jam, Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($jam);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'section' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $section = Jam::normalizeSection($validated['section']);

        $pivot = DB::table('jam_pattern')
            ->where('jam_id', $jam->id)
            ->where('pattern_id', $pattern->id)
            ->first();

        abort_if($pivot === null, 404);

        $sectionChanged = $pivot->section !== $section;

        $position = (int) $pivot->position;

        if ($sectionChanged) {
            $position = (int) DB::table('jam_pattern')
                ->where('jam_id', $jam->id)
                ->where('section', $section)
                ->max('position') + 1;
        }

        $jam->patterns()->updateExistingPivot($pattern->id, [
            'section' => $section,
            'position' => $position,
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('jams.show', $jam)->with('status', 'Pattern placement updated.');
    }

    public function movePatternUp(Jam $jam, Pattern $pattern): RedirectResponse
    {
        return $this->movePattern($jam, $pattern, 'up');
    }

    public function movePatternDown(Jam $jam, Pattern $pattern): RedirectResponse
    {
        return $this->movePattern($jam, $pattern, 'down');
    }

    public function detachPattern(Jam $jam, Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($jam);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $jam->patterns()->detach($pattern->id);

        return redirect()->route('jams.show', $jam)->with('status', 'Pattern removed from jam.');
    }

    private function movePattern(Jam $jam, Pattern $pattern, string $direction): RedirectResponse
    {
        $this->ensureOwner($jam);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $current = DB::table('jam_pattern')
            ->where('jam_id', $jam->id)
            ->where('pattern_id', $pattern->id)
            ->first();

        abort_if($current === null, 404);

        $neighborQuery = DB::table('jam_pattern')
            ->where('jam_id', $jam->id)
            ->where('section', $current->section);

        if ($direction === 'up') {
            $neighbor = $neighborQuery
                ->where('position', '<', $current->position)
                ->orderByDesc('position')
                ->first();
        } else {
            $neighbor = $neighborQuery
                ->where('position', '>', $current->position)
                ->orderBy('position')
                ->first();
        }

        if ($neighbor !== null) {
            DB::transaction(function () use ($current, $neighbor): void {
                DB::table('jam_pattern')
                    ->where('id', $current->id)
                    ->update(['position' => $neighbor->position]);

                DB::table('jam_pattern')
                    ->where('id', $neighbor->id)
                    ->update(['position' => $current->position]);
            });
        }

        return redirect()->route('jams.show', $jam);
    }

    private function ensureOwner(Jam $jam): void
    {
        abort_if($jam->user_id !== auth()->id(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJam(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:50'],
            'tempo' => ['nullable', 'integer', 'min:20', 'max:300'],
            'style' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
