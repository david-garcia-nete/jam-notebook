<?php

namespace Tests\Feature;

use App\Models\Jam;
use App\Models\Pattern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiJamDevelopmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_jam_develop_page(): void
    {
        $jam = Jam::factory()->create();

        $this->get(route('jams.develop.create', $jam))->assertRedirect('/login');
    }

    public function test_owner_can_view_jam_develop_page(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create(['title' => 'Song Draft']);

        $this->actingAs($user)
            ->get(route('jams.develop.create', $jam))
            ->assertOk()
            ->assertSee('Develop Jam with AI')
            ->assertSee('Song Draft');
    }

    public function test_non_owner_is_forbidden_from_jam_develop_page(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $jam = Jam::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('jams.develop.create', $jam))
            ->assertForbidden();
    }

    public function test_owner_can_generate_jam_development_preview_with_mocked_ai_response(): void
    {
        config()->set('services.openai.key', 'test-key');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'suggestions' => [
                        [
                            'type' => 'new_section',
                            'section' => 'Chorus',
                            'title' => null,
                            'instrument' => null,
                            'content' => null,
                            'notes' => null,
                            'description' => 'Add a strong melodic hook',
                            'from_section' => null,
                            'to_section' => null,
                        ],
                        [
                            'type' => 'new_pattern',
                            'section' => 'Chorus',
                            'title' => 'Chorus Hook',
                            'instrument' => 'guitar',
                            'content' => 'Power chords with octave melody.',
                            'notes' => 'High energy',
                            'description' => null,
                            'from_section' => null,
                            'to_section' => null,
                        ],
                        [
                            'type' => 'transition',
                            'section' => null,
                            'title' => null,
                            'instrument' => null,
                            'content' => null,
                            'notes' => null,
                            'description' => 'Build with a drum fill.',
                            'from_section' => 'Verse',
                            'to_section' => 'Chorus',
                        ],
                    ],
                ]),
            ]),
        ]);

        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create([
            'title' => 'Verse Riff',
            'content' => 'Am - F - C - G',
        ]);
        $jam->patterns()->attach($pattern, ['section' => 'Verse', 'position' => 1, 'notes' => 'Keep it tight']);

        $response = $this->actingAs($user)->post(route('jams.develop.store', $jam), [
            'instruction' => 'Make it more energetic.',
        ]);

        $response->assertOk();
        $response->assertSee('AI Jam Suggestions');
        $response->assertSee('Chorus Hook');
        $response->assertSee('Build with a drum fill.');
    }

    public function test_save_creates_new_patterns_and_does_not_modify_original_patterns(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $original = Pattern::factory()->for($user)->create([
            'title' => 'Original Verse',
            'content' => 'Original verse content',
        ]);
        $jam->patterns()->attach($original, ['section' => 'Verse', 'position' => 1]);

        $suggestions = [
            'suggestions' => [
                [
                    'type' => 'new_pattern',
                    'section' => 'Chorus',
                    'title' => 'Chorus Hook',
                    'instrument' => 'guitar',
                    'content' => 'Big chorus chords',
                    'notes' => 'Open strums',
                    'description' => null,
                    'from_section' => null,
                    'to_section' => null,
                ],
                [
                    'type' => 'transition',
                    'section' => null,
                    'title' => null,
                    'instrument' => null,
                    'content' => null,
                    'notes' => null,
                    'description' => 'Snare fill into chorus',
                    'from_section' => 'Verse',
                    'to_section' => 'Chorus',
                ],
            ],
        ];

        $this->actingAs($user)->post(route('jams.develop.save', $jam), [
            'suggestions_json' => json_encode($suggestions),
            'selected' => [0, 1],
            'attach_to_jam' => '1',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('patterns', [
            'user_id' => $user->id,
            'title' => 'Chorus Hook',
            'content' => 'Big chorus chords',
        ]);

        $this->assertDatabaseHas('patterns', [
            'user_id' => $user->id,
            'title' => 'Transition: Verse → Chorus',
            'content' => 'Snare fill into chorus',
        ]);

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'section' => 'Chorus',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'section' => 'Chorus',
            'position' => 2,
        ]);

        $original->refresh();
        $this->assertSame('Original Verse', $original->title);
        $this->assertSame('Original verse content', $original->content);
    }

    public function test_unknown_suggestion_type_is_ignored_on_save(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $suggestions = [
            'suggestions' => [
                [
                    'type' => 'unexpected_type',
                    'title' => 'Should Be Ignored',
                    'content' => 'No-op',
                ],
            ],
        ];

        $this->actingAs($user)->post(route('jams.develop.save', $jam), [
            'suggestions_json' => json_encode($suggestions),
            'selected' => [0],
            'attach_to_jam' => '1',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseCount('patterns', 0);
        $this->assertDatabaseCount('jam_pattern', 0);
    }

    public function test_empty_transition_is_not_saved(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $suggestions = [
            'suggestions' => [
                [
                    'type' => 'transition',
                    'description' => '   ',
                    'from_section' => 'Verse',
                    'to_section' => 'Chorus',
                ],
            ],
        ];

        $this->actingAs($user)->post(route('jams.develop.save', $jam), [
            'suggestions_json' => json_encode($suggestions),
            'selected' => [0],
            'attach_to_jam' => '1',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseMissing('patterns', [
            'user_id' => $user->id,
            'title' => 'Transition: Verse → Chorus',
        ]);
        $this->assertDatabaseCount('jam_pattern', 0);
    }

    public function test_ai_suggestions_create_new_patterns_and_attach_without_duplicate_existing_rows(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $existing = Pattern::factory()->for($user)->create([
            'title' => 'Transition: Verse → Chorus',
            'type' => 'arrangement idea',
            'content' => 'Lift into chorus',
            'notes' => 'From Verse to Chorus',
        ]);
        $jam->patterns()->attach($existing, ['section' => 'Chorus', 'position' => 1]);

        $suggestions = [
            'suggestions' => [
                [
                    'type' => 'transition',
                    'description' => 'Lift into chorus',
                    'from_section' => 'Verse',
                    'to_section' => 'Chorus',
                ],
            ],
        ];

        $this->actingAs($user)->post(route('jams.develop.save', $jam), [
            'suggestions_json' => json_encode($suggestions),
            'selected' => [0],
            'attach_to_jam' => '1',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseCount('jam_pattern', 2);
        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $existing->id,
            'section' => 'Chorus',
            'position' => 1,
        ]);
        $this->assertDatabaseHas('patterns', [
            'user_id' => $user->id,
            'title' => 'Transition: Verse → Chorus',
            'content' => 'Lift into chorus',
        ]);
    }

    public function test_relaxed_schema_accepts_minimal_suggestion_with_only_type(): void
    {
        config()->set('services.openai.key', 'test-key');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'suggestions' => [
                        ['type' => 'new_section'],
                    ],
                ]),
            ]),
        ]);

        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jams.develop.store', $jam), [
            'instruction' => 'Only minimal suggestions',
        ]);

        $response->assertOk()->assertSee('AI Jam Suggestions');

        Http::assertSent(function ($request): bool {
            $required = data_get(
                $request->data(),
                'text.format.schema.properties.suggestions.items.required'
            );

            return $required === ['type'];
        });
    }
}
