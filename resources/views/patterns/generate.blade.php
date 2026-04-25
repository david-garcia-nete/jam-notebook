<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generate Pattern with AI</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <p class="text-sm text-gray-600">Describe the idea you want to practice, and AI will draft a playable pattern you can edit.</p>

                    <form method="POST" action="{{ route('patterns.generate.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="prompt" :value="__('Prompt')" />
                            <textarea id="prompt" name="prompt" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('prompt') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('prompt')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="type" :value="__('Type (optional)')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>

                            <div>
                                <x-input-label for="instrument" :value="__('Instrument (optional)')" />
                                <select id="instrument" name="instrument" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any instrument</option>
                                    @foreach ($instruments as $instrument)
                                        <option value="{{ $instrument }}" @selected(old('instrument') === $instrument)>{{ ucfirst($instrument) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('instrument')" />
                            </div>

                            <div>
                                <x-input-label for="key" :value="__('Key (optional)')" />
                                <x-text-input id="key" name="key" type="text" class="mt-1 block w-full" :value="old('key')" />
                                <x-input-error class="mt-2" :messages="$errors->get('key')" />
                            </div>

                            <div>
                                <x-input-label for="tempo" :value="__('Tempo (BPM, optional)')" />
                                <x-text-input id="tempo" name="tempo" type="number" min="20" max="300" class="mt-1 block w-full" :value="old('tempo')" />
                                <x-input-error class="mt-2" :messages="$errors->get('tempo')" />
                            </div>

                            <div>
                                <x-input-label for="style" :value="__('Style (optional)')" />
                                <x-text-input id="style" name="style" type="text" class="mt-1 block w-full" :value="old('style')" />
                                <x-input-error class="mt-2" :messages="$errors->get('style')" />
                            </div>

                            <div>
                                <x-input-label for="difficulty" :value="__('Difficulty (optional)')" />
                                <select id="difficulty" name="difficulty" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any difficulty</option>
                                    @foreach ($difficulties as $difficulty)
                                        <option value="{{ $difficulty }}" @selected(old('difficulty') === $difficulty)>{{ ucfirst($difficulty) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('difficulty')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Generate Pattern</x-primary-button>
                            <a href="{{ route('patterns.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
