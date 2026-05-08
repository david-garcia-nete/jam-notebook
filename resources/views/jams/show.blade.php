<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $jam->title }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('jams.develop.create', $jam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-100 border border-indigo-200 rounded-md text-xs uppercase tracking-widest text-indigo-700 hover:bg-indigo-200">Develop with AI</a>
                <a href="{{ route('jams.sheet', $jam) }}" class="inline-flex items-center px-4 py-2 bg-emerald-100 border border-emerald-200 rounded-md text-xs uppercase tracking-widest text-emerald-700 hover:bg-emerald-200">Jam Sheet</a>
                <a href="{{ route('jams.edit', $jam) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Edit Jam</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <h3 class="font-semibold text-gray-900">Jam Details</h3>
                <p class="text-sm text-gray-600">
                    @if ($jam->key)
                        Key: {{ $jam->key }}
                    @endif
                    @if ($jam->tempo)
                        @if ($jam->key) · @endif
                        {{ $jam->tempo }} BPM
                    @endif
                    @if ($jam->style)
                        @if ($jam->tempo || $jam->key) · @endif
                        {{ $jam->style }}
                    @endif
                </p>
                @if ($jam->notes)
                    <p class="text-gray-800">{{ $jam->notes }}</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Add Pattern</h3>
                @if ($availablePatterns->isNotEmpty())
                    <form method="POST" action="{{ route('jams.patterns.attach', $jam) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 md:items-end">
                        @csrf
                        <div class="md:col-span-2">
                            <x-input-label for="pattern_id" :value="__('Pattern')" />
                            <select id="pattern_id" name="pattern_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select a pattern</option>
                                @foreach ($availablePatterns as $pattern)
                                    <option value="{{ $pattern->id }}">{{ $pattern->title }} ({{ $pattern->instrument ?: 'no instrument' }})</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('pattern_id')" />
                        </div>
                        <div>
                            <x-input-label for="section" :value="__('Section')" />
                            <select id="section" name="section" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach (\App\Models\Jam::SECTIONS as $section)
                                    <option value="{{ $section }}" @selected(old('section') === $section)>{{ $section }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('section')" />
                        </div>
                        <div class="md:col-span-3">
                            <x-primary-button>Add to Jam</x-primary-button>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-gray-600">All your patterns are already in this jam or you don’t have any yet.</p>
                @endif
            </div>

            @php
                $patternsBySection = $jam->patterns->groupBy(fn ($pattern) => $pattern->pivot->section ?: 'Unsectioned');
            @endphp

            <div class="space-y-4">
                <h3 class="font-semibold text-gray-900">Patterns in this Jam</h3>
                @forelse ($patternsBySection as $section => $patterns)
                    <div class="space-y-3">
                        <h4 class="text-md font-semibold text-gray-800">{{ $section }}</h4>

                        @foreach ($patterns as $pattern)
                            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h5 class="text-lg font-semibold text-gray-900">{{ $pattern->title }}</h5>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $pattern->type ?: 'Uncategorized' }}
                                            · {{ $pattern->instrument ?: 'No instrument' }}
                                            · Position {{ $pattern->pivot->position }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('jams.patterns.detach', [$jam, $pattern]) }}" onsubmit="return confirm('Remove this pattern from jam?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                </div>

                                @include('patterns.partials.content', ['content' => $pattern->content])

                                <form method="POST" action="{{ route('jams.patterns.update', [$jam, $pattern]) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @csrf
                                    <div>
                                        <x-input-label for="section_{{ $pattern->id }}" :value="__('Section')" />
                                        <select id="section_{{ $pattern->id }}" name="section" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                            @foreach (\App\Models\Jam::SECTIONS as $section)
                                                <option value="{{ $section }}" @selected($pattern->pivot->section === $section)>{{ $section }}</option>
                                            @endforeach
                                            @if ($pattern->pivot->section && ! in_array($pattern->pivot->section, \App\Models\Jam::SECTIONS, true))
                                                <option value="{{ $pattern->pivot->section }}" selected>{{ $pattern->pivot->section }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="notes_{{ $pattern->id }}" :value="__('Notes')" />
                                        <textarea id="notes_{{ $pattern->id }}" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $pattern->pivot->notes }}</textarea>
                                    </div>
                                    <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                                        <x-primary-button>Save Placement</x-primary-button>
                                    </div>
                                </form>

                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('jams.patterns.move-up', [$jam, $pattern]) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Move Up</button>
                                    </form>
                                    <form method="POST" action="{{ route('jams.patterns.move-down', [$jam, $pattern]) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Move Down</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-gray-600">
                        No patterns in this jam yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
