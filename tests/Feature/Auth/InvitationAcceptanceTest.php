<?php

namespace Tests\Feature\Auth;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_acceptance_screen_can_be_rendered(): void
    {
        $owner = User::factory()->create();
        $inviter = User::factory()->create();

        $invitation = TeamMember::create([
            'owner_id' => $owner->id,
            'invited_by' => $inviter->id,
            'name' => 'Invited User',
            'email' => 'invitee@example.com',
            'role' => 'member',
            'status' => 'pending',
            'invitation_token' => Str::random(32),
        ]);

        $response = $this->get('/invitations/accept/'.$invitation->invitation_token);

        $response->assertStatus(200);
    }
}
