<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pattern</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('patterns.update', $pattern) }}">
                        @method('PUT')
                        @php($submitLabel = 'Update Pattern')
                        @include('patterns._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
