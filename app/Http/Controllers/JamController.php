<?php

namespace App\Http\Controllers;

use App\Models\Jam;
use App\Models\Pattern;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $jam->load(['patterns' => fn ($query) => $query->where('user_id', auth()->id())->latest()]);

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
        ]);

        $pattern = Pattern::query()->findOrFail($validated['pattern_id']);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $jam->patterns()->syncWithoutDetaching([$pattern->id]);

        return redirect()->route('jams.show', $jam)->with('status', 'Pattern added to jam.');
    }

    public function detachPattern(Jam $jam, Pattern $pattern): RedirectResponse
    {
        $this->ensureOwner($jam);
        abort_if($pattern->user_id !== auth()->id(), 403);

        $jam->patterns()->detach($pattern->id);

        return redirect()->route('jams.show', $jam)->with('status', 'Pattern removed from jam.');
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
