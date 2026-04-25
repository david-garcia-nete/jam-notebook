<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Jam</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('jams.update', $jam) }}">
                    @method('PUT')
                    @include('jams._form', ['submitLabel' => 'Update Jam'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
