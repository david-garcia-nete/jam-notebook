<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pattern Details</h2>
            <a href="{{ route('patterns.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Pattern Library</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900">{{ $pattern->title }}</h3>
                        <p class="text-sm text-gray-600 mt-2">
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

                <div class="overflow-x-auto rounded-md bg-gray-50 border border-gray-200 p-4">
                    <pre class="m-0 text-sm text-gray-800 font-mono whitespace-pre">{{ $pattern->content }}</pre>
                </div>

                @if ($pattern->notes)
                    <div>
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Notes</h4>
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $pattern->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
