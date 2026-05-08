@props([
    'content',
    'preview' => false,
])

<div class="overflow-x-auto rounded-md border border-gray-200 bg-gray-50 p-4">
    @if ($preview)
        <div class="relative max-h-60 overflow-hidden">
            <pre class="m-0 text-sm leading-relaxed text-gray-800 font-mono whitespace-pre">{{ $content }}</pre>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-gray-50 to-transparent"></div>
        </div>
        <p class="mt-1 text-right text-xs text-gray-500" aria-hidden="true">…</p>
    @else
        <pre class="m-0 text-sm leading-relaxed text-gray-800 font-mono whitespace-pre">{{ $content }}</pre>
    @endif
</div>
