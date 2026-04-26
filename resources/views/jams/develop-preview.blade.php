<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">AI Jam Suggestions</h2>
            <a href="{{ route('jams.develop.create', $jam) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </x-slot>


    <div class="py-8">
        @php
            $allSuggestions = $suggestions['suggestions'] ?? [];
            $hasSuggestions = is_array($allSuggestions) && count($allSuggestions) > 0;
        @endphp
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <h3 class="font-semibold text-gray-900">{{ $jam->title }}</h3>
                @if ($instruction)
                    <p class="text-sm text-gray-600">Instruction: {{ $instruction }}</p>
                @endif
                @if ($hasSuggestions)
                    <p class="text-sm text-gray-600">Select suggestions to save as new patterns.</p>
                @else
                    <p class="text-sm text-gray-600">No usable AI suggestions were returned. Try regenerating with a more specific instruction.</p>
                @endif
            </div>

            @if ($hasSuggestions)
                <form method="POST" action="{{ route('jams.develop.save', $jam) }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="suggestions_json" value="{{ json_encode($suggestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Section Ideas</h3>
                    </div>
                    @forelse (($suggestions['suggestions'] ?? []) as $index => $item)
                        @continue(($item['type'] ?? null) !== 'new_section')
                        <label class="flex items-start gap-3 border border-gray-200 rounded-md p-4">
                            <input type="checkbox" name="selected[]" value="{{ $index }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                            <span>
                                <span class="font-medium text-gray-900">{{ $item['section'] ?? 'New section' }} Section Idea</span>
                                <span class="block text-sm text-gray-600">Will create a Pattern in {{ $item['section'] ?? 'this section' }}.</span>
                                <span class="block text-sm text-gray-700 mt-1">{{ $item['description'] ?? '' }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-600">No section idea suggestions.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900">New Patterns</h3>
                    @forelse (($suggestions['suggestions'] ?? []) as $index => $item)
                        @continue(($item['type'] ?? null) !== 'new_pattern')
                        <label class="flex items-start gap-3 border border-gray-200 rounded-md p-4">
                            <input type="checkbox" name="selected[]" value="{{ $index }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                            <span>
                                <span class="font-medium text-gray-900">{{ $item['title'] ?? 'Untitled' }}</span>
                                <span class="block text-sm text-gray-600">{{ $item['section'] ?? 'Verse' }} · {{ $item['instrument'] ?? 'No instrument' }}</span>
                                <span class="block text-sm text-gray-700 mt-1">{{ $item['content'] ?? '' }}</span>
                                @if (! empty($item['notes']))
                                    <span class="block text-sm text-gray-600 mt-1">Notes: {{ $item['notes'] }}</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-600">No new pattern suggestions.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900">Transitions</h3>
                    @forelse (($suggestions['suggestions'] ?? []) as $index => $item)
                        @continue(($item['type'] ?? null) !== 'transition')
                        <label class="flex items-start gap-3 border border-gray-200 rounded-md p-4">
                            <input type="checkbox" name="selected[]" value="{{ $index }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                            <span>
                                <span class="font-medium text-gray-900">{{ $item['from_section'] ?? 'Section' }} → {{ $item['to_section'] ?? 'Section' }}</span>
                                <span class="block text-sm text-gray-700 mt-1">{{ $item['description'] ?? '' }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-600">No transition suggestions.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="attach_to_jam" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Attach created patterns to this jam automatically
                    </label>
                </div>

                    <x-primary-button>Save Selected Suggestions</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
