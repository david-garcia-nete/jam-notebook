<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Develop Pattern with AI</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Original Pattern</h3>
                <p class="text-sm text-gray-600">
                    {{ $pattern->type ?: 'Uncategorized' }} · {{ $pattern->instrument ?: 'No instrument' }}
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
                <p class="text-gray-900 font-medium">{{ $pattern->title }}</p>
                <div class="overflow-x-auto">
                    <pre class="m-0 text-gray-800 font-mono whitespace-pre">{{ $pattern->content }}</pre>
                </div>
                @if ($pattern->notes)
                    <p class="text-sm text-gray-600 whitespace-pre-line">Notes: {{ $pattern->notes }}</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <p class="text-sm text-gray-600">Add an optional instruction to steer the variation, extension, or companion idea.</p>

                    <form method="POST" action="{{ route('patterns.develop.store', $pattern) }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="instruction" :value="__('Instruction (optional)')" />
                            <textarea id="instruction" name="instruction" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Make this more reggae with a slightly harder right-hand rhythm.">{{ old('instruction') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('instruction')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Develop Pattern</x-primary-button>
                            <a href="{{ route('patterns.edit', $pattern) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
