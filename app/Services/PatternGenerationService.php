<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PatternGenerationService
{
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
