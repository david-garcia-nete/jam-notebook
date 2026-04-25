<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Develop Jam with AI</h2>
            <a href="{{ route('jams.show', $jam) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Back to Jam</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <h3 class="text-lg font-semibold text-gray-900">{{ $jam->title }}</h3>
                <p class="text-sm text-gray-600">Review your jam structure, then ask AI for arrangement ideas.</p>
            </div>

            @php
                $patternsBySection = $jam->patterns->groupBy(fn ($pattern) => $pattern->pivot->section ?: 'Unsectioned');
            @endphp

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Jam Structure</h3>
                @forelse ($patternsBySection as $section => $patterns)
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $section }}</h4>
                        <ul class="mt-2 space-y-2">
                            @foreach ($patterns as $pattern)
                                <li class="border border-gray-200 rounded-md p-3">
                                    <p class="font-medium text-gray-900">{{ $pattern->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $pattern->instrument ?: 'No instrument' }} · Position {{ $pattern->pivot->position }}</p>
                                    <p class="text-sm text-gray-700 mt-1">{{ \Illuminate\Support\Str::limit($pattern->content, 180) }}</p>
                                    @if ($pattern->pivot->notes)
                                        <p class="text-sm text-gray-600 mt-1">Notes: {{ $pattern->pivot->notes }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">This jam does not have patterns yet. Add some patterns first to get better AI suggestions.</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('jams.develop.store', $jam) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="instruction" :value="__('Instruction (optional)')" />
                        <textarea id="instruction" name="instruction" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. make it more energetic">{{ old('instruction') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('instruction')" />
                    </div>
                    <x-primary-button>Generate Ideas</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
