<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jams</h2>
            <a href="{{ route('jams.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">New Jam</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            @forelse ($jams as $jam)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <a href="{{ route('jams.show', $jam) }}" class="text-lg font-semibold text-gray-900 hover:text-indigo-700">{{ $jam->title }}</a>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $jam->patterns_count }} pattern{{ $jam->patterns_count === 1 ? '' : 's' }}
                                @if ($jam->key)
                                    · Key: {{ $jam->key }}
                                @endif
                                @if ($jam->tempo)
                                    · {{ $jam->tempo }} BPM
                                @endif
                                @if ($jam->style)
                                    · {{ $jam->style }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <a href="{{ route('jams.edit', $jam) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            <form method="POST" action="{{ route('jams.destroy', $jam) }}" onsubmit="return confirm('Delete this jam?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-gray-600">
                    You don’t have any jams yet. Create one to combine your ideas.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
