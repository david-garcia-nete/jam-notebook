@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $jam->title ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-input-label for="key" :value="__('Key')" />
            <x-text-input id="key" name="key" type="text" class="mt-1 block w-full" :value="old('key', $jam->key ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('key')" />
        </div>

        <div>
            <x-input-label for="tempo" :value="__('Tempo (BPM)')" />
            <x-text-input id="tempo" name="tempo" type="number" min="20" max="300" class="mt-1 block w-full" :value="old('tempo', $jam->tempo ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('tempo')" />
        </div>

        <div>
            <x-input-label for="style" :value="__('Style')" />
            <x-text-input id="style" name="style" type="text" class="mt-1 block w-full" :value="old('style', $jam->style ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('style')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $jam->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('jams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</div>
