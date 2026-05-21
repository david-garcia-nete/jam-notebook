@props([
    'tablature',
    'preview' => false,
])

<div class="overflow-x-auto rounded-md border border-gray-200 bg-gray-50 p-4">
    @if ($preview)
        <div class="relative max-h-48 overflow-hidden">
            <pre class="m-0 text-sm leading-relaxed text-gray-800 font-mono whitespace-pre">{{ $tablature }}</pre>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-gray-50 to-transparent"></div>
        </div>
    @else
        <pre class="m-0 text-sm leading-relaxed text-gray-800 font-mono whitespace-pre">{{ $tablature }}</pre>
    @endif
</div>
