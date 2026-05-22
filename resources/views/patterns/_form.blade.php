@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $pattern->title ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="content" :value="__('Description')" />
        <textarea id="content" name="content" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content', $pattern->content ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('content')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="type" :value="__('Type')" />
            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select type</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" @selected(old('type', $pattern->type ?? '') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('type')" />
        </div>

        <div>
            <x-input-label for="instrument" :value="__('Instrument')" />
            <select id="instrument" name="instrument" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select instrument</option>
                @foreach ($instruments as $instrument)
                    <option value="{{ $instrument }}" @selected(old('instrument', $pattern->instrument ?? '') === $instrument)>{{ ucfirst($instrument) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('instrument')" />
        </div>

        <div>
            <x-input-label for="key" :value="__('Key')" />
            <x-text-input id="key" name="key" type="text" class="mt-1 block w-full" :value="old('key', $pattern->key ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('key')" />
        </div>

        <div>
            <x-input-label for="tempo" :value="__('Tempo (BPM)')" />
            <x-text-input id="tempo" name="tempo" type="number" min="20" max="300" class="mt-1 block w-full" :value="old('tempo', $pattern->tempo ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('tempo')" />
        </div>

        <div>
            <x-input-label for="style" :value="__('Style')" />
            <x-text-input id="style" name="style" type="text" class="mt-1 block w-full" :value="old('style', $pattern->style ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('style')" />
        </div>

        <div>
            <x-input-label for="difficulty" :value="__('Difficulty')" />
            <select id="difficulty" name="difficulty" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select difficulty</option>
                @foreach ($difficulties as $difficulty)
                    <option value="{{ $difficulty }}" @selected(old('difficulty', $pattern->difficulty ?? '') === $difficulty)>{{ ucfirst($difficulty) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('difficulty')" />
        </div>
    </div>


    <div>
        <x-input-label for="notation_url" :value="__('Notation Link')" />
        <x-text-input id="notation_url" name="notation_url" type="url" class="mt-1 block w-full" :value="old('notation_url', $pattern->notation_url ?? '')" placeholder="https://" />
        <x-input-error class="mt-2" :messages="$errors->get('notation_url')" />
    </div>

    <div>
        <x-input-label for="embed_code" :value="__('Embed Code')" />
        <textarea id="embed_code" name="embed_code" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('embed_code', $pattern->embed_code ?? '') }}</textarea>
        <p class="mt-1 text-sm text-gray-500">Paste official embed code from MuseScore, YouTube, SoundCloud, etc. Optional.</p>
        <x-input-error class="mt-2" :messages="$errors->get('embed_code')" />
    </div>

    <div>
        <x-input-label for="notes" :value="__('Tablature / Notes')" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $pattern->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('patterns.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</div>
