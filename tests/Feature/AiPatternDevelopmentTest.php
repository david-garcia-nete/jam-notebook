<?php

namespace Tests\Feature;

use App\Models\Pattern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPatternDevelopmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_develop_page(): void
    {
        $pattern = Pattern::factory()->create();

        $this->get(route('patterns.develop.create', $pattern))->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_develop_form_for_own_pattern(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create(['title' => 'Seed Pattern']);

        $this->actingAs($user)
            ->get(route('patterns.develop.create', $pattern))
            ->assertOk()
            ->assertSee('Develop Pattern with AI')
            ->assertSee('Seed Pattern');
    }

    public function test_authenticated_user_cannot_develop_another_users_pattern(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $pattern = Pattern::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('patterns.develop.create', $pattern))
            ->assertForbidden();
    }

    public function test_authenticated_user_can_generate_developed_preview_with_mocked_openai_response(): void
    {
        config()->set('services.openai.key', 'test-key');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'title' => 'Reggae Comping Variation',
                    'type' => 'chord progression',
                    'instrument' => 'piano',
                    'key' => 'A minor',
                    'tempo' => 88,
                    'style' => 'reggae',
                    'difficulty' => 'intermediate',
                    'content' => 'Variation: play Am7 - Dm7 - G - C with short offbeat stabs...',
                    'notes' => 'Keep left hand light and practice with metronome on beats 2 and 4.',
                ]),
            ]),
        ]);

        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('patterns.develop.store', $pattern), [
            'instruction' => 'Make this more reggae.',
        ]);

        $response->assertOk();
        $response->assertSee('Reggae Comping Variation');
        $response->assertSee('Am7 - Dm7 - G - C');
        $response->assertSee('Original Pattern');
    }

    public function test_authenticated_user_can_save_developed_pattern_as_a_new_pattern(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create(['title' => 'Original Seed']);

        $response = $this->actingAs($user)->post(route('patterns.develop.save', $pattern), [
            'title' => 'Reggae Comping Variation',
            'type' => 'chord progression',
            'instrument' => 'piano',
            'key' => 'A minor',
            'tempo' => 88,
            'style' => 'reggae',
            'difficulty' => 'intermediate',
            'content' => 'Variation: play Am7 - Dm7 - G - C with short offbeat stabs...',
            'notes' => 'Keep left hand light and practice with metronome on beats 2 and 4.',
        ]);

        $response->assertRedirect(route('patterns.index'));
        $response->assertSessionHas('status', 'Developed pattern saved.');

        $this->assertDatabaseHas('patterns', [
            'title' => 'Reggae Comping Variation',
            'user_id' => $user->id,
        ]);

        $this->assertSame(2, Pattern::where('user_id', $user->id)->count());
    }

    public function test_saving_developed_pattern_does_not_modify_original_pattern(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create([
            'title' => 'Original Seed',
            'content' => 'Original content',
        ]);

        $this->actingAs($user)->post(route('patterns.develop.save', $pattern), [
            'title' => 'Developed Variation',
            'type' => 'chord progression',
            'instrument' => 'piano',
            'key' => 'C',
            'tempo' => 92,
            'style' => 'reggae',
            'difficulty' => 'intermediate',
            'content' => 'Developed content',
            'notes' => 'Practice slowly.',
        ])->assertRedirect(route('patterns.index'));

        $pattern->refresh();

        $this->assertSame('Original Seed', $pattern->title);
        $this->assertSame('Original content', $pattern->content);

        $this->assertDatabaseHas('patterns', [
            'title' => 'Developed Variation',
            'content' => 'Developed content',
            'user_id' => $user->id,
        ]);
    }
}
