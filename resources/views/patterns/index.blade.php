<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pattern Library</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('patterns.generate.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-100 border border-indigo-200 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-200">Generate with AI</a>
                <a href="{{ route('patterns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">New Pattern</a>
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

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('patterns.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="instrument" :value="__('Instrument')" />
                        <select id="instrument" name="instrument" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All</option>
                            @foreach ($instruments as $instrument)
                                <option value="{{ $instrument }}" @selected(($filters['instrument'] ?? '') === $instrument)>{{ ucfirst($instrument) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="style" :value="__('Style')" />
                        <x-text-input id="style" name="style" type="text" class="mt-1 block w-full" :value="$filters['style'] ?? ''" />
                    </div>
                    <div class="flex gap-2">
                        <x-primary-button>Filter</x-primary-button>
                        <a href="{{ route('patterns.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($patterns as $pattern)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $pattern->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $pattern->type ?: 'Uncategorized' }}
                                    · {{ $pattern->instrument ?: 'No instrument' }}
                                    @if ($pattern->key)
                                        · Key: {{ $pattern->key }}
                                    @endif
                                    @if ($pattern->tempo)
                                        · {{ $pattern->tempo }} BPM
                                    @endif
                                    @if ($pattern->style)
                                        · {{ $pattern->style }}
                                    @endif
                                    @if ($pattern->difficulty)
                                        · {{ ucfirst($pattern->difficulty) }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('patterns.develop.create', $pattern) }}" class="text-emerald-600 hover:text-emerald-800">Develop with AI</a>
                                <a href="{{ route('patterns.edit', $pattern) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                <form method="POST" action="{{ route('patterns.destroy', $pattern) }}" onsubmit="return confirm('Delete this pattern?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <pre class="m-0 text-sm text-gray-800 font-mono whitespace-pre">{{ \Illuminate\Support\Str::limit($pattern->content, 200) }}</pre>
                        </div>

                        @if ($pattern->notes)
                            <p class="text-sm text-gray-600">Notes: {{ \Illuminate\Support\Str::limit($pattern->notes, 120) }}</p>
                        @endif
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-gray-600">
                        You don’t have any patterns yet. Start by capturing your first idea.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
