<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generated Pattern Preview</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <p class="text-sm text-gray-600">Review and edit this idea before saving it to your Pattern Library.</p>

                    <form method="POST" action="{{ route('patterns.generate.save') }}">
                        @php($pattern = (object) $generated)
                        @php($submitLabel = 'Save Generated Pattern')
                        @include('patterns._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
