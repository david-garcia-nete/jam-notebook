<?php

namespace Tests\Feature;

use App\Models\Pattern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatternTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_from_patterns_index(): void
    {
        $this->get('/patterns')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_a_pattern(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/patterns', [
            'title' => 'Warmup chords',
            'content' => 'C - Am - F - G',
            'type' => 'chord progression',
            'instrument' => 'piano',
            'key' => 'C',
            'tempo' => 120,
            'style' => 'pop',
            'difficulty' => 'beginner',
            'notation_url' => 'https://example.com/notation/warmup',
            'notes' => 'Try in 3/4 next.',
        ]);

        $response->assertRedirect(route('patterns.index'));

        $this->assertDatabaseHas('patterns', [
            'user_id' => $user->id,
            'title' => 'Warmup chords',
            'content' => 'C - Am - F - G',
            'notation_url' => 'https://example.com/notation/warmup',
        ]);
    }

    public function test_authenticated_user_can_see_their_own_patterns(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create(['title' => 'My Idea']);

        $response = $this->actingAs($user)->get('/patterns');

        $response->assertOk();
        $response->assertSee($pattern->title);
    }

    public function test_authenticated_user_cannot_see_another_users_patterns(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPattern = Pattern::factory()->for($otherUser)->create(['title' => 'Private Idea']);

        $response = $this->actingAs($user)->get('/patterns');

        $response->assertOk();
        $response->assertDontSee($otherPattern->title);
    }

    public function test_authenticated_user_cannot_edit_another_users_pattern(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPattern = Pattern::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('patterns.edit', $otherPattern))
            ->assertForbidden();
    }

    public function test_pattern_library_includes_view_link(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create([
            'notation_url' => 'https://example.com/notation/library-link',
        ]);

        $this->actingAs($user)
            ->get(route('patterns.index'))
            ->assertOk()
            ->assertSee(route('patterns.show', $pattern), false)
            ->assertSee('View')
            ->assertSee('Open notation')
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_authenticated_user_can_view_full_pattern_content_on_show_page(): void
    {
        $user = User::factory()->create();
        $content = "E|----------------|\nB|--3--5--7--8----|\nG|----------------|";
        $pattern = Pattern::factory()->for($user)->create([
            'title' => 'Long Tab',
            'content' => $content,
            'notation_url' => 'https://example.com/notation/long-tab',
            'notes' => 'Play slowly first.',
        ]);

        $this->actingAs($user)
            ->get(route('patterns.show', $pattern))
            ->assertOk()
            ->assertSee('Long Tab')
            ->assertSee($content)
            ->assertSee('Play slowly first.')
            ->assertSee('Develop with AI')
            ->assertSee('Open notation')
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('Edit')
            ->assertSee('Delete');
    }

    public function test_authenticated_user_cannot_view_another_users_pattern(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherPattern = Pattern::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('patterns.show', $otherPattern))
            ->assertForbidden();
    }

    public function test_authenticated_user_can_update_their_own_pattern(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create([
            'title' => 'Old Title',
            'content' => 'Old content',
        ]);

        $response = $this->actingAs($user)->put(route('patterns.update', $pattern), [
            'title' => 'New Title',
            'content' => 'Updated pattern content',
            'type' => 'melody',
            'instrument' => 'synth',
            'key' => 'D minor',
            'tempo' => 95,
            'style' => 'ambient',
            'difficulty' => 'intermediate',
            'notation_url' => 'https://example.com/notation/new-title',
            'notes' => 'Use arpeggiator.',
        ]);

        $response->assertRedirect(route('patterns.index'));

        $this->assertDatabaseHas('patterns', [
            'id' => $pattern->id,
            'title' => 'New Title',
            'content' => 'Updated pattern content',
            'instrument' => 'synth',
            'notation_url' => 'https://example.com/notation/new-title',
        ]);
    }


    public function test_notation_url_must_be_a_valid_url_and_within_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/patterns', [
            'title' => 'Bad notation',
            'content' => 'C - F - G',
            'notation_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('notation_url');
    }

    public function test_authenticated_user_can_delete_their_own_pattern(): void
    {
        $user = User::factory()->create();
        $pattern = Pattern::factory()->for($user)->create([
            'notation_url' => 'https://example.com/notation/library-link',
        ]);

        $response = $this->actingAs($user)->delete(route('patterns.destroy', $pattern));

        $response->assertRedirect(route('patterns.index'));
        $this->assertDatabaseMissing('patterns', ['id' => $pattern->id]);
    }

    public function test_index_filters_patterns_by_type_instrument_and_style(): void
    {
        $user = User::factory()->create();

        $matching = Pattern::factory()->for($user)->create([
            'title' => 'Match',
            'type' => 'drum groove',
            'instrument' => 'drums',
            'style' => 'funk',
        ]);

        Pattern::factory()->for($user)->create([
            'title' => 'Wrong Type',
            'type' => 'melody',
            'instrument' => 'drums',
            'style' => 'funk',
        ]);

        Pattern::factory()->for($user)->create([
            'title' => 'Wrong Instrument',
            'type' => 'drum groove',
            'instrument' => 'bass',
            'style' => 'funk',
        ]);

        Pattern::factory()->for($user)->create([
            'title' => 'Wrong Style',
            'type' => 'drum groove',
            'instrument' => 'drums',
            'style' => 'rock',
        ]);

        $response = $this->actingAs($user)->get('/patterns?type=drum+groove&instrument=drums&style=funk');

        $response->assertOk();
        $response->assertSee($matching->title);
        $response->assertDontSee('Wrong Type');
        $response->assertDontSee('Wrong Instrument');
        $response->assertDontSee('Wrong Style');
    }

    public function test_pattern_library_preview_renders_longer_multiline_ascii_content(): void
    {
        $user = User::factory()->create();
        $content = "HH|x-x-x-x-x-x-x-x-|
SD|----o-------o---|
KD|o-------o-o-----|
T1|------o-------o-|
T2|--o-------o-----|
CY|--------x-------|
";

        Pattern::factory()->for($user)->create([
            'title' => 'Pocket Groove',
            'content' => $content,
        ]);

        $this->actingAs($user)
            ->get(route('patterns.index'))
            ->assertOk()
            ->assertSee('T2|--o-------o-----|', false)
            ->assertSee('CY|--------x-------|', false)
            ->assertSee('whitespace-pre-line', false)
            ->assertSee('max-h-60', false)
            ->assertSee('…', false);
    }

}
