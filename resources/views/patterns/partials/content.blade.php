@props([
    'content',
    'preview' => false,
])

@if ($preview)
    <div class="relative max-h-60 overflow-hidden">
        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $content }}</p>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white to-transparent"></div>
    </div>
    <p class="mt-1 text-right text-xs text-gray-500" aria-hidden="true">…</p>
@else
    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $content }}</p>
@endif
