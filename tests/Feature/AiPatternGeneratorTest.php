<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPatternGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_generator_form(): void
    {
        $this->get('/patterns/generate')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_generator_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('patterns.generate.create'))
            ->assertOk()
            ->assertSee('Generate Pattern');
    }

    public function test_prompt_is_required_for_generation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('patterns.generate.store'), [])
            ->assertSessionHasErrors(['prompt']);
    }

    public function test_authenticated_user_can_generate_pattern_preview(): void
    {
        config()->set('services.openai.key', 'test-key');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'title' => 'Funky Pocket Loop',
                    'type' => 'drum groove',
                    'instrument' => 'drums',
                    'key' => null,
                    'tempo' => 96,
                    'style' => 'funk',
                    'difficulty' => 'intermediate',
                    'content' => 'Kick on 1 and the & of 2, snare on 2 and 4, closed hats on eighths.',
                    'notes' => 'Accent ghost notes on the snare for feel.',
                ]),
            ]),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('patterns.generate.store'), [
            'prompt' => 'Give me a tight funk drum idea for 4 bars.',
            'instrument' => 'drums',
            'style' => 'funk',
            'difficulty' => 'intermediate',
        ]);

        $response->assertOk();
        $response->assertSee('Funky Pocket Loop');
        $response->assertSee('Kick on 1 and the & of 2');
    }

    public function test_authenticated_user_can_save_generated_pattern(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('patterns.generate.save'), [
            'title' => 'Generated Groove',
            'type' => 'drum groove',
            'instrument' => 'drums',
            'key' => null,
            'tempo' => 102,
            'style' => 'funk',
            'difficulty' => 'intermediate',
            'content' => 'Kick-snare-hat pattern for steady groove practice.',
            'notes' => 'Loop for 10 minutes and add fills.',
        ]);

        $response->assertRedirect(route('patterns.index'));
        $response->assertSessionHas('status', 'Generated pattern saved.');

        $this->assertDatabaseHas('patterns', [
            'title' => 'Generated Groove',
            'content' => 'Kick-snare-hat pattern for steady groove practice.',
            'user_id' => $user->id,
        ]);
    }

    public function test_saved_generated_pattern_belongs_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post(route('patterns.generate.save'), [
            'title' => 'Owned Pattern',
            'type' => 'melody',
            'instrument' => 'guitar',
            'content' => 'Minor pentatonic phrase over Am groove.',
        ])->assertRedirect(route('patterns.index'));

        $this->assertDatabaseHas('patterns', [
            'title' => 'Owned Pattern',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('patterns', [
            'title' => 'Owned Pattern',
            'user_id' => $otherUser->id,
        ]);
    }
}
