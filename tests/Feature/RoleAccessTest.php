<?php

namespace Tests\Feature;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_role_cannot_access_admin_endpoints(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        TeamMember::create([
            'owner_id' => $owner->id,
            'invited_by' => $owner->id,
            'user_id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'role' => 'member',
            'status' => 'accepted',
            'invitation_token' => 'token-member',
        ]);

        Sanctum::actingAs($member);

        $this->getJson('/api/dashboard-stats')->assertOk();
        $this->getJson('/api/usage')->assertStatus(403);
        $this->getJson('/api/provider-keys')->assertStatus(403);
    }

    public function test_admin_role_can_access_admin_endpoints(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();

        TeamMember::create([
            'owner_id' => $owner->id,
            'invited_by' => $owner->id,
            'user_id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
            'status' => 'accepted',
            'invitation_token' => 'token-admin',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/dashboard-stats')->assertOk();
        $this->getJson('/api/usage')->assertStatus(403);
        $this->getJson('/api/provider-keys')->assertOk();
    }
}
