<?php

namespace Tests\Feature;

use App\Mail\TeamInvitationMail;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamInviteLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_starter_users_cannot_invite_team_members(): void
    {
        $this->configurePlans();
        Mail::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/team/invitations', [
            'name' => 'Starter Invite',
            'email' => 'starter-invite@example.com',
            'role' => 'member',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Upgrade the plan to invite team members.']);
    }

    public function test_professional_plan_is_limited_to_five_invites(): void
    {
        $this->configurePlans();
        Mail::fake();

        $user = User::factory()->create();
        $this->attachSubscription($user, 'price_pro');

        for ($i = 0; $i < 5; $i++) {
            TeamMember::create([
                'owner_id' => $user->id,
                'invited_by' => $user->id,
                'user_id' => null,
                'name' => 'Member '.$i,
                'email' => "member{$i}@example.com",
                'role' => 'member',
                'status' => 'pending',
                'invitation_token' => Str::random(40),
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/team/invitations', [
            'name' => 'Sixth Member',
            'email' => 'member5@example.com',
            'role' => 'member',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Team member limit reached. Upgrade your plan to invite more.']);
    }

    public function test_enterprise_plan_allows_unlimited_invites(): void
    {
        $this->configurePlans();
        Mail::fake();

        $user = User::factory()->create();
        $this->attachSubscription($user, 'price_enterprise');

        for ($i = 0; $i < 10; $i++) {
            TeamMember::create([
                'owner_id' => $user->id,
                'invited_by' => $user->id,
                'user_id' => null,
                'name' => 'Member '.$i,
                'email' => "ent-member{$i}@example.com",
                'role' => 'member',
                'status' => 'pending',
                'invitation_token' => Str::random(40),
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/team/invitations', [
            'name' => 'Extra Member',
            'email' => 'extra-member@example.com',
            'role' => 'member',
        ]);

        $response->assertStatus(201);
        Mail::assertSent(TeamInvitationMail::class);
    }

    private function configurePlans(): void
    {
        config([
            'subscriptions.plans.starter.price_id' => 'price_starter',
            'subscriptions.plans.pro.price_id' => 'price_pro',
            'subscriptions.plans.enterprise.price_id' => 'price_enterprise',
            'subscriptions.default_plan' => 'starter',
        ]);
    }

    private function attachSubscription(User $user, string $priceId): void
    {
        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.$priceId,
            'stripe_status' => 'active',
            'stripe_price' => $priceId,
            'quantity' => 1,
        ]);
    }
}
