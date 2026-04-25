<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Developed Pattern Preview</h2>
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
                <p class="text-gray-800 whitespace-pre-line">{{ $pattern->content }}</p>
                @if ($pattern->notes)
                    <p class="text-sm text-gray-600 whitespace-pre-line">Notes: {{ $pattern->notes }}</p>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <p class="text-sm text-gray-600">Review and edit the AI-developed result before saving it as a new pattern.</p>

                    <form method="POST" action="{{ route('patterns.develop.save', $pattern) }}">
                        @php($pattern = (object) $generated)
                        @php($submitLabel = 'Save Developed Pattern')
                        @include('patterns._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
