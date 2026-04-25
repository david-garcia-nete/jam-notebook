<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $jam->title }}</h2>
            <a href="{{ route('jams.edit', $jam) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Edit Jam</a>
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
                    <form method="POST" action="{{ route('jams.patterns.attach', $jam) }}" class="flex flex-col md:flex-row gap-3 md:items-end">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="pattern_id" :value="__('Pattern')" />
                            <select id="pattern_id" name="pattern_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select a pattern</option>
                                @foreach ($availablePatterns as $pattern)
                                    <option value="{{ $pattern->id }}">{{ $pattern->title }} ({{ $pattern->instrument ?: 'no instrument' }})</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('pattern_id')" />
                        </div>
                        <x-primary-button>Add to Jam</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">All your patterns are already in this jam or you don’t have any yet.</p>
                @endif
            </div>

            <div class="space-y-4">
                <h3 class="font-semibold text-gray-900">Patterns in this Jam</h3>
                @forelse ($jam->patterns as $pattern)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $pattern->title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $pattern->type ?: 'Uncategorized' }}
                                    · {{ $pattern->instrument ?: 'No instrument' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('jams.patterns.detach', [$jam, $pattern]) }}" onsubmit="return confirm('Remove this pattern from jam?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remove</button>
                            </form>
                        </div>

                        <p class="text-gray-800">{{ \Illuminate\Support\Str::limit($pattern->content, 200) }}</p>
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
