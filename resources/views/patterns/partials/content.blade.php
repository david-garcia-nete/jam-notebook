@props([
    'content',
    'preview' => false,
])

<div class="rounded-md border border-gray-200 bg-gray-50 p-4">
    @if ($preview)
        <div class="relative max-h-40 overflow-hidden">
            <p class="m-0 text-sm leading-relaxed text-gray-800 whitespace-pre-line">{{ $content }}</p>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-gray-50 to-transparent"></div>
        </div>
    @else
        <p class="m-0 text-sm leading-relaxed text-gray-800 whitespace-pre-line">{{ $content }}</p>
    @endif
</div>
