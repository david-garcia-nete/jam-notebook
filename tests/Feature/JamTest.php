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

    public function test_user_can_attach_pattern_to_jam(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
        ]);

        $response->assertRedirect(route('jams.show', $jam));
        $this->assertDatabaseHas('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
        ]);

        $this->actingAs($user)->post(route('jams.patterns.attach', $jam), [
            'pattern_id' => $pattern->id,
        ])->assertRedirect(route('jams.show', $jam));

        $this->assertDatabaseCount('jam_pattern', 1);
    }

    public function test_user_can_detach_pattern_from_jam(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create();
        $jam->patterns()->attach($pattern);

        $response = $this->actingAs($user)->delete(route('jams.patterns.detach', [$jam, $pattern]));

        $response->assertRedirect(route('jams.show', $jam));
        $this->assertDatabaseMissing('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $pattern->id,
        ]);
    }

    public function test_attached_patterns_appear_on_jam_show_page(): void
    {
        $user = User::factory()->create();
        $jam = Jam::factory()->for($user)->create();
        $pattern = Pattern::factory()->for($user)->create([
            'title' => 'Chord Loop',
            'content' => 'Dm - Bb - F - C',
        ]);
        $jam->patterns()->attach($pattern);

        $response = $this->actingAs($user)->get(route('jams.show', $jam));

        $response->assertOk();
        $response->assertSee('Chord Loop');
        $response->assertSee('Dm - Bb - F - C');
    }

    public function test_pattern_must_belong_to_user_when_attaching(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $jam = Jam::factory()->for($user)->create();
        $otherPattern = Pattern::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->post(route('jams.patterns.attach', $jam), ['pattern_id' => $otherPattern->id])
            ->assertForbidden();

        $this->assertDatabaseMissing('jam_pattern', [
            'jam_id' => $jam->id,
            'pattern_id' => $otherPattern->id,
        ]);
    }
}
