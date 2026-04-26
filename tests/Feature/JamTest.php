<?php

namespace Tests\Feature;

use App\Models\Jam;
use App\Models\Pattern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JamTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_jams(): void
    {
        $jam = Jam::factory()->create();

        $this->get('/jams')->assertRedirect('/login');
        $this->get(route('jams.show', $jam))->assertRedirect('/login');
        $this->post('/jams', ['title' => 'Nope'])->assertRedirect('/login');
    }

    public function test_user_can_create_jam(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/jams', [
            'title' => 'Practice Session A',
            'key' => 'E minor',
            'tempo' => 98,
            'style' => 'indie',
            'notes' => 'Try with palm-muted guitar.',
        ]);

        $response->assertRedirect(route('jams.index'));

        $this->assertDatabaseHas('jams', [
            'user_id' => $user->id,
            'title' => 'Practice Session A',
            'tempo' => 98,
        ]);
    }

    public function test_user_sees_only_their_jams(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myJam = Jam::factory()->for($user)->create(['title' => 'My Jam']);
        $otherJam = Jam::factory()->for($otherUser)->create(['title' => 'Private Jam']);

        $response = $this->actingAs($user)->get('/jams');

        $response->assertOk();
        $response->assertSee($myJam->title);
        $response->assertDontSee($otherJam->title);
    }

    public function test_user_cannot_view_another_users_jam(): void
    {
        $user = User::factory()->create();
        $otherJam = Jam::factory()->create();

        $this->actingAs($user)
            ->get(route('jams.show', $otherJam))
            ->assertForbidden();
    }

    public function test_user_can_update_their_jam(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create(['title' => 'Old Jam']);

        $response = $this->actingAs($user)->put(route('jams.update', $jam), [
            'title' => 'Updated Jam',
            'key' => 'A',
            'tempo' => 120,
            'style' => 'funk',
            'notes' => 'More syncopation.',
        ]);

        $response->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('jams', [
            'id' => $jam->id,
            'title' => 'Updated Jam',
            'style' => 'funk',
        ]);
    }

    public function test_user_can_delete_their_jam(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('jams.destroy', $jam));

        $response->assertRedirect(route('jams.index'));
        $this->assertDatabaseMissing('jams', ['id' => $jam->id]);
    }

    public function test_user_can_attach_pattern_to_jam_with_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
            'section' => 'Intro',
        ]);

        $response->assertRedirect(route('jams.show', $jam));
        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
            'section' => 'Intro',
            'position' => 1,
        ]);

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
            'section' => 'Intro',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseCount('jam_pattern', 1);
    }

    public function test_pattern_position_auto_increments_within_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $patternA = Pattern::factory()->for($user)->create();
        $patternB = Pattern::factory()->for($user)->create();

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $patternA->id,
            'section' => 'Verse',
        ]);

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $patternB->id,
            'section' => 'Verse',
        ]);

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $patternA->id,
            'section' => 'Verse',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $patternB->id,
            'section' => 'Verse',
            'position' => 2,
        ]);
    }

    public function test_attaching_pattern_normalizes_lowercase_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
            'section' => 'verse',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
            'section' => 'Verse',
            'position' => 1,
        ]);
    }

    public function test_updating_placement_normalizes_uppercase_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();
        $jam->patterns()->attach($pattern, ['section' => 'Verse', 'position' => 1]);

        $this->actingAs($user)->post(route('jams.patterns.update', [$jam, $pattern]), [
            'section' => 'CHORUS',
            'notes' => 'big ending',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
            'section' => 'Chorus',
            'position' => 1,
            'notes' => 'big ending',
        ]);
    }

    public function test_attaching_pattern_normalizes_pre_chorus_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
            'section' => 'pre-chorus',
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
            'section' => 'Pre-Chorus',
            'position' => 1,
        ]);
    }

    public function test_user_can_detach_pattern_from_jam(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();
        $jam->patterns()->attach($pattern, ['section' => 'Verse', 'position' => 1]);

        $response = $this->actingAs($user)->delete(route('jams.patterns.detach', [$jam, $pattern]));

        $response->assertRedirect(route('jams.show', $jam));
        $this->assertDatabaseMissing('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
        ]);
    }

    public function test_grouped_patterns_appear_on_jam_show_page(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $introPattern = Pattern::factory()->for($user)->create([
            'title' => 'Intro Drone',
            'content' => 'A A A A',
        ]);
        $chorusPattern = Pattern::factory()->for($user)->create([
            'title' => 'Chorus Hook',
            'content' => 'Dm - Bb - F - C',
        ]);

        $jam->patterns()->attach($introPattern, ['section' => 'Intro', 'position' => 1]);
        $jam->patterns()->attach($chorusPattern, ['section' => 'Chorus', 'position' => 1]);

        $response = $this->actingAs($user)->get(route('jams.show', $jam));

        $response->assertOk();
        $response->assertSee('Intro');
        $response->assertSee('Chorus');
        $response->assertSee('Intro Drone');
        $response->assertSee('Chorus Hook');
    }

    public function test_jam_show_page_displays_sections_in_musical_order(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();

        $chorusPattern = Pattern::factory()->for($user)->create(['title' => 'Chorus Pattern']);
        $interludePattern = Pattern::factory()->for($user)->create(['title' => 'Interlude Pattern']);
        $bridgePattern = Pattern::factory()->for($user)->create(['title' => 'Bridge Pattern']);
        $introPattern = Pattern::factory()->for($user)->create(['title' => 'Intro Pattern']);
        $versePattern = Pattern::factory()->for($user)->create(['title' => 'Verse Pattern']);

        $jam->patterns()->attach($chorusPattern, ['section' => 'Chorus', 'position' => 1]);
        $jam->patterns()->attach($interludePattern, ['section' => 'Interlude', 'position' => 1]);
        $jam->patterns()->attach($bridgePattern, ['section' => 'Bridge', 'position' => 1]);
        $jam->patterns()->attach($introPattern, ['section' => 'Intro', 'position' => 1]);
        $jam->patterns()->attach($versePattern, ['section' => 'Verse', 'position' => 1]);

        $response = $this->actingAs($user)->get(route('jams.show', $jam));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Intro Pattern',
            'Verse Pattern',
            'Chorus Pattern',
            'Interlude Pattern',
            'Bridge Pattern',
        ]);
    }

    public function test_jam_show_page_displays_section_dropdown_options_in_order(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        Pattern::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('jams.show', $jam));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Intro',
            'Verse',
            'Pre-Chorus',
            'Chorus',
            'Interlude',
            'Bridge',
            'Solo',
            'Outro',
        ]);
    }

    public function test_reordering_swaps_position_within_same_section(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $patternA = Pattern::factory()->for($user)->create(['title' => 'First']);
        $patternB = Pattern::factory()->for($user)->create(['title' => 'Second']);

        $jam->patterns()->attach($patternA, ['section' => 'Verse', 'position' => 1]);
        $jam->patterns()->attach($patternB, ['section' => 'Verse', 'position' => 2]);

        $this->actingAs($user)
            ->post(route('jams.patterns.move-down', [$jam, $patternA]))
            ->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $patternA->id,
            'section' => 'Verse',
            'position' => 2,
        ]);

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $patternB->id,
            'section' => 'Verse',
            'position' => 1,
        ]);
    }

    public function test_user_cannot_modify_another_users_jam_placement(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $jam = Jam::factory()->for($owner)->create();
        $pattern = Pattern::factory()->for($owner)->create();
        $jam->patterns()->attach($pattern, ['section' => 'Verse', 'position' => 1]);

        $this->actingAs($intruder)
            ->post(route('jams.patterns.update', [$jam, $pattern]), [
                'section' => 'Chorus',
                'notes' => 'Unauthorized',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('jams.patterns.move-up', [$jam, $pattern]))
            ->assertForbidden();

        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
            'section' => 'Verse',
            'position' => 1,
        ]);
    }

    public function test_pattern_must_belong_to_user_when_attaching(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $jam = Jam::factory()->for($user)->create();
        $otherPattern = Pattern::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->post(route('jams.patterns.attach', $jam), ['pattern_id' => $otherPattern->id, 'section' => 'Intro'])
            ->assertForbidden();

        $this->assertDatabaseMissing('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $otherPattern->id,
        ]);
    }
}
