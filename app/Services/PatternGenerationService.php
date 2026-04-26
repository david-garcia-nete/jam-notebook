<?php

namespace App\Services;

use App\Models\Jam;
use App\Models\Pattern;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PatternGenerationService
{
    private const DEVELOP_JAM_ALLOWED_SUGGESTION_TYPES = [
        'new_section',
        'new_pattern',
        'transition',
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array
    {
        $apiKey = config('services.openai.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('OpenAI API key is not configured. Set OPENAI_API_KEY in your environment.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'You are a helpful musician and songwriting practice partner inside Jam Notebook. Generate practical, playable, text-first musical ideas. Do not include audio, notation, MIDI, tabs, or DAW-specific exports. Return only structured JSON matching the requested fields.',
                            ],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $this->buildPrompt($input),
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'pattern',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string'],
                                'type' => ['type' => ['string', 'null']],
                                'instrument' => ['type' => ['string', 'null']],
                                'key' => ['type' => ['string', 'null']],
                                'tempo' => ['type' => ['integer', 'null']],
                                'style' => ['type' => ['string', 'null']],
                                'difficulty' => ['type' => ['string', 'null']],
                                'content' => ['type' => 'string'],
                                'notes' => ['type' => ['string', 'null']],
                            ],
                            'required' => ['title', 'type', 'instrument', 'key', 'tempo', 'style', 'difficulty', 'content', 'notes'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        $response->throw();

        return $this->normalizeGeneratedPattern($this->extractJsonPayload($response->json()));
    }

    /**
     * @return array<string, mixed>
     */
    public function develop(Pattern $pattern, ?string $instruction = null): array
    {
        $apiKey = config('services.openai.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('OpenAI API key is not configured. Set OPENAI_API_KEY in your environment.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'You are a practical musician and jam partner inside Jam Notebook. Develop existing ideas into playable next-step patterns. Avoid audio, notation, MIDI, tabs, DAW export, or production-heavy instructions. Return only structured JSON matching the requested fields.',
                            ],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $this->buildDevelopPrompt($pattern, $instruction),
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'pattern',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string'],
                                'type' => ['type' => ['string', 'null']],
                                'instrument' => ['type' => ['string', 'null']],
                                'key' => ['type' => ['string', 'null']],
                                'tempo' => ['type' => ['integer', 'null']],
                                'style' => ['type' => ['string', 'null']],
                                'difficulty' => ['type' => ['string', 'null']],
                                'content' => ['type' => 'string'],
                                'notes' => ['type' => ['string', 'null']],
                            ],
                            'required' => ['title', 'type', 'instrument', 'key', 'tempo', 'style', 'difficulty', 'content', 'notes'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        $response->throw();

        return $this->normalizeGeneratedPattern($this->extractJsonPayload($response->json()));
    }



    /**
     * @return array<string, mixed>
     */
    public function developJam(Jam $jam, ?string $instruction = null): array
    {
        $apiKey = config('services.openai.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('OpenAI API key is not configured. Set OPENAI_API_KEY in your environment.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'You are a practical songwriting assistant inside Jam Notebook. Help users develop complete song arrangements from jam structures. Return only strict JSON matching the schema.',
                            ],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $this->buildDevelopJamPrompt($jam, $instruction),
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'jam_development',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'suggestions' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'type' => ['type' => 'string'],
                                            'section' => ['type' => ['string', 'null']],
                                            'title' => ['type' => ['string', 'null']],
                                            'instrument' => ['type' => ['string', 'null']],
                                            'content' => ['type' => ['string', 'null']],
                                            'notes' => ['type' => ['string', 'null']],
                                            'description' => ['type' => ['string', 'null']],
                                            'from_section' => ['type' => ['string', 'null']],
                                            'to_section' => ['type' => ['string', 'null']],
                                        ],
                                        'required' => [
                                            'type',
                                            'section',
                                            'title',
                                            'instrument',
                                            'content',
                                            'notes',
                                            'description',
                                            'from_section',
                                            'to_section',
                                        ],
                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],
                            'required' => ['suggestions'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        $response->throw();

        return $this->normalizeDevelopJamSuggestions($this->extractJsonPayload($response->json()));
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function buildPrompt(array $input): string
    {
        $parts = [
            'User prompt: '.(string) ($input['prompt'] ?? ''),
            'Optional hints:',
            '- type: '.$this->valueOrAuto($input['type'] ?? null),
            '- instrument: '.$this->valueOrAuto($input['instrument'] ?? null),
            '- key: '.$this->valueOrAuto($input['key'] ?? null),
            '- tempo: '.$this->valueOrAuto($input['tempo'] ?? null),
            '- style: '.$this->valueOrAuto($input['style'] ?? null),
            '- difficulty: '.$this->valueOrAuto($input['difficulty'] ?? null),
            'Make it practical for practice/jamming. Include a concrete musical idea and clear usage notes.',
        ];

        return implode("\n", $parts);
    }


    private function buildDevelopPrompt(Pattern $pattern, ?string $instruction): string
    {
        $parts = [
            'Develop this existing musical idea into a useful next-step pattern.',
            'Keep it playable and practical.',
            'Provide a variation, extension, or complementary idea.',
            'Do not replace creativity; help the musician practice and expand the idea.',
            '',
            'Original pattern:',
            '- title: '.$pattern->title,
            '- type: '.$this->valueOrAuto($pattern->type),
            '- instrument: '.$this->valueOrAuto($pattern->instrument),
            '- key: '.$this->valueOrAuto($pattern->key),
            '- tempo: '.$this->valueOrAuto($pattern->tempo),
            '- style: '.$this->valueOrAuto($pattern->style),
            '- difficulty: '.$this->valueOrAuto($pattern->difficulty),
            '- content: '.$pattern->content,
            '- notes: '.$this->valueOrAuto($pattern->notes),
            '',
            'User instruction: '.($instruction !== null && trim($instruction) !== '' ? trim($instruction) : 'none'),
            '',
            'For content and notes, include practical sections where appropriate:',
            '- What changed',
            '- Variation or extension',
            '- How to practice it',
            '- Optional companion idea',
        ];

        return implode("\n", $parts);
    }



    private function buildDevelopJamPrompt(Jam $jam, ?string $instruction): string
    {
        $sections = $jam->patterns
            ->groupBy(fn (Pattern $pattern) => (string) ($pattern->pivot->section ?: 'Unsectioned'))
            ->map(fn ($patterns, $section) => [
                'name' => $section,
                'patterns' => $patterns->map(fn (Pattern $pattern) => [
                    'title' => $pattern->title,
                    'content' => $pattern->content,
                    'instrument' => $pattern->instrument,
                    'notes' => $pattern->pivot->notes,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $payload = [
            'jam' => [
                'title' => $jam->title,
                'sections' => $sections,
            ],
            'instruction' => $instruction !== null && trim($instruction) !== '' ? trim($instruction) : null,
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function valueOrAuto(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'auto';
        }

        return (string) $value;
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    private function extractJsonPayload(array $json): array
    {
        $outputText = Arr::get($json, 'output_text');

        if (is_string($outputText) && $outputText !== '') {
            $decoded = json_decode($outputText, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $output = Arr::get($json, 'output', []);

        if (is_array($output)) {
            foreach ($output as $item) {
                $content = Arr::get($item, 'content', []);
                if (! is_array($content)) {
                    continue;
                }

                foreach ($content as $contentItem) {
                    $text = Arr::get($contentItem, 'text');
                    if (is_string($text) && $text !== '') {
                        $decoded = json_decode($text, true);
                        if (is_array($decoded)) {
                            return $decoded;
                        }
                    }
                }
            }
        }

        throw new RuntimeException('OpenAI response did not contain usable JSON for pattern generation.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeGeneratedPattern(array $data): array
    {
        $title = $this->normalizeString($data['title'] ?? null, false);
        $content = $this->normalizeString($data['content'] ?? null, false);

        if ($title === null || $content === null) {
            throw new RuntimeException('OpenAI returned unusable pattern data. Title and content are required.');
        }

        return [
            'title' => $title,
            'type' => $this->normalizeString($data['type'] ?? null),
            'instrument' => $this->normalizeString($data['instrument'] ?? null),
            'key' => $this->normalizeString($data['key'] ?? null),
            'tempo' => $this->normalizeTempo($data['tempo'] ?? null),
            'style' => $this->normalizeString($data['style'] ?? null),
            'difficulty' => $this->normalizeDifficulty($data['difficulty'] ?? null),
            'content' => $content,
            'notes' => $this->normalizeString($data['notes'] ?? null),
        ];
    }



    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeDevelopJamSuggestions(array $data): array
    {
        $rawSuggestions = $data['suggestions'] ?? [];

        if (! is_array($rawSuggestions)) {
            throw new RuntimeException('OpenAI returned unusable jam development data.');
        }

        $suggestions = [];

        foreach ($rawSuggestions as $suggestion) {
            if (! is_array($suggestion)) {
                continue;
            }

            $type = $this->normalizeString($suggestion['type'] ?? null, false);

            if ($type === null) {
                continue;
            }

            if (! in_array($type, self::DEVELOP_JAM_ALLOWED_SUGGESTION_TYPES, true)) {
                continue;
            }

            $suggestions[] = [
                'type' => $type,
                'section' => $this->normalizeString($suggestion['section'] ?? null),
                'title' => $this->normalizeString($suggestion['title'] ?? null),
                'instrument' => $this->normalizeString($suggestion['instrument'] ?? null),
                'content' => $this->normalizeString($suggestion['content'] ?? null),
                'notes' => $this->normalizeString($suggestion['notes'] ?? null),
                'description' => $this->normalizeString($suggestion['description'] ?? null),
                'from_section' => $this->normalizeString($suggestion['from_section'] ?? null),
                'to_section' => $this->normalizeString($suggestion['to_section'] ?? null),
            ];
        }

        return ['suggestions' => $suggestions];
    }

    private function normalizeString(mixed $value, bool $nullable = true): ?string
    {
        if ($value === null) {
            return $nullable ? null : null;
        }

        if (! is_string($value)) {
            $value = (string) $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return $nullable ? null : null;
        }

        return $trimmed;
    }

    private function normalizeTempo(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function normalizeDifficulty(mixed $value): ?string
    {
        $difficulty = $this->normalizeString($value);

        if ($difficulty === null) {
            return null;
        }

        return in_array($difficulty, ['beginner', 'intermediate', 'advanced'], true) ? $difficulty : null;
    }
}
