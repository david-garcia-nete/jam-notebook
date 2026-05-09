<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jam Sheet: {{ $jam->title }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('jams.show', $jam) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-50">Back to Jam</a>
                <button type="button" onclick="copyJamSheet()" class="inline-flex items-center px-4 py-2 bg-blue-100 border border-blue-200 rounded-md text-xs uppercase tracking-widest text-blue-700 hover:bg-blue-200">Copy Text</button>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-emerald-100 border border-emerald-200 rounded-md text-xs uppercase tracking-widest text-emerald-700 hover:bg-emerald-200">Print</button>
            </div>
        </div>
    </x-slot>

    @php
        $patternsBySection = $jam->patterns->groupBy(fn ($pattern) => $pattern->pivot->section ?: 'Unsectioned');
    @endphp

    <div class="py-8 jam-sheet">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3 print:shadow-none print:ring-0 print:p-0">
                <h1 class="text-2xl font-bold text-gray-900">{{ $jam->title }}</h1>
                <p class="text-sm text-gray-700">
                    @if ($jam->key)
                        Key: {{ $jam->key }}
                    @endif
                    @if ($jam->tempo)
                        @if ($jam->key) · @endif
                        Tempo: {{ $jam->tempo }} BPM
                    @endif
                    @if ($jam->style)
                        @if ($jam->tempo || $jam->key) · @endif
                        Style: {{ $jam->style }}
                    @endif
                </p>
                @if ($jam->notes)
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Jam Notes</h2>
                        <p class="text-gray-800 whitespace-pre-line">{{ $jam->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                @forelse ($patternsBySection as $section => $patterns)
                    <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3 print:shadow-none print:ring-0 print:p-0 print:mt-6 print-break-inside-avoid">
                        <h2 class="text-xl font-semibold text-gray-900 border-b border-gray-200 pb-1">{{ $section }}</h2>

                        <div class="space-y-4">
                            @foreach ($patterns as $pattern)
                                <article class="pt-1 print-break-inside-avoid">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $pattern->title }}</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $pattern->type ?: 'Uncategorized' }}
                                        · {{ $pattern->instrument ?: 'No instrument' }}
                                        · Position {{ $pattern->pivot->position }}
                                    </p>
                                    <div class="mt-2">
                                        @include('patterns.partials.content', ['content' => $pattern->content])
                                    </div>
                                    @if ($pattern->notes)
                                        <p class="mt-2 text-sm text-gray-600"><span class="font-semibold">Pattern Notes:</span> {{ $pattern->notes }}</p>
                                    @endif
                                    @if ($pattern->pivot->notes)
                                        <p class="mt-2 text-sm text-gray-700"><span class="font-semibold">Placement Notes:</span> {{ $pattern->pivot->notes }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-gray-600 print:shadow-none print:ring-0 print:p-0">
                        No patterns in this jam yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <pre id="jam-sheet-text" class="hidden">{{
trim(
    collect([
        $jam->title,
        collect([
            $jam->key ? 'Key: '.$jam->key : null,
            $jam->tempo ? 'Tempo: '.$jam->tempo.' BPM' : null,
            $jam->style ? 'Style: '.$jam->style : null,
        ])->filter()->join(' | '),
        $jam->notes ? "Jam Notes:\n{$jam->notes}" : null,
        $patternsBySection->isEmpty()
            ? 'No patterns in this jam yet.'
            : $patternsBySection->map(function ($patterns, $section) {
                $sectionText = "\n{$section}\n";

                return $sectionText.$patterns->map(function ($pattern) {
                    $metadata = collect([
                        $pattern->type ?: 'Uncategorized',
                        $pattern->instrument ?: 'No instrument',
                        'Position '.$pattern->pivot->position,
                    ])->join(' | ');

                    $patternNotes = $pattern->notes ? "\nPattern Notes: {$pattern->notes}" : '';
                    $placementNotes = $pattern->pivot->notes ? "\nPlacement Notes: {$pattern->pivot->notes}" : '';

                    return "- {$pattern->title}\n  {$metadata}\n{$pattern->content}{$patternNotes}{$placementNotes}";
                })->join("\n\n");
            })->join("\n\n"),
    ])->filter()->join("\n\n")
)
}}</pre>

    <style>
        @media print {
            nav,
            .print\:hidden,
            header,
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
                color: #111827;
                font-size: 12pt;
                line-height: 1.4;
            }

            .jam-sheet {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            .print-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>

    <script>
        async function copyJamSheet() {
            const text = document.getElementById('jam-sheet-text').textContent.trim();

            try {
                await navigator.clipboard.writeText(text);
                alert('Jam sheet text copied to clipboard.');
            } catch (error) {
                alert('Unable to copy automatically. Please copy manually from the page.');
            }
        }
    </script>
</x-app-layout>
