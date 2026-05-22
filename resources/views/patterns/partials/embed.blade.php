@php
    $rawEmbedCode = trim((string) $pattern->embed_code);
    $sanitizedEmbedCode = $pattern->sanitizedEmbedCode();
@endphp

@if ($sanitizedEmbedCode)
    <div class="space-y-2" data-testid="pattern-embed">
        <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Embed</h4>
        <div class="overflow-x-auto rounded-md border border-gray-200 bg-gray-50 p-3">
            {!! $sanitizedEmbedCode !!}
        </div>
    </div>
@elseif (filled($rawEmbedCode))
    <p class="text-sm text-amber-700">Embed code could not be rendered.</p>
@endif
