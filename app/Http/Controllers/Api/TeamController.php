<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\AiProviderKey;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Subscription;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        $ownerId = $actor->teamOwnerId();
        $owner = $ownerId === $actor->id ? $actor : User::findOrFail($ownerId);
        $inviteLimit = $this->inviteLimitFor($owner);
        $currentInvites = TeamMember::where('owner_id', $ownerId)->count();
        $canInvite = $inviteLimit === null ? true : $currentInvites < $inviteLimit;
        $seatLimit = $inviteLimit === null ? null : $inviteLimit + 1;

        $memberUserIds = TeamMember::where('owner_id', $ownerId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        $userIds = collect([$owner->id])
            ->concat($memberUserIds)
            ->unique()
            ->values();

        $queriesByUser = AiProviderKey::whereIn('user_id', $userIds)
            ->selectRaw('user_id, COALESCE(SUM(total_queries), 0) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $invitations = TeamMember::where('owner_id', $ownerId)
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TeamMember $member) use ($queriesByUser) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->role,
                    'status' => $member->status,
                    'is_owner' => false,
                    'total_queries' => (int) ($queriesByUser[$member->user_id] ?? 0),
                    'accepted_at' => $member->accepted_at,
                    'invited_at' => $member->created_at,
                ];
            });

        $members = collect([[
            'id' => $owner->id,
            'name' => $owner->name,
            'email' => $owner->email,
            'role' => 'owner',
            'status' => 'active',
            'is_owner' => true,
            'total_queries' => (int) ($queriesByUser[$owner->id] ?? 0),
            'accepted_at' => $owner->email_verified_at,
        ]])->concat($invitations)->values();

        return response()->json([
            'plan_name' => $this->planForUser($owner)['name'] ?? null,
            'invite_limit' => $inviteLimit,
            'seat_limit' => $seatLimit,
            'can_invite' => $canInvite,
            'members' => $members,
        ]);
    }

    public function invite(Request $request)
    {
        $actor = $request->user();
        $ownerId = $actor->teamOwnerId();
        $owner = $ownerId === $actor->id ? $actor : User::findOrFail($ownerId);
        $inviteLimit = $this->inviteLimitFor($owner);
        $currentInvites = TeamMember::where('owner_id', $ownerId)->count();

        if ($inviteLimit !== null && $currentInvites >= $inviteLimit) {
            $message = $inviteLimit === 0
                ? 'Upgrade the plan to invite team members.'
                : 'Team member limit reached. Upgrade your plan to invite more.';

            return response()->json(['message' => $message], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::notIn([$owner->email]),
                Rule::unique('team_members', 'email')->where(fn ($q) => $q->where('owner_id', $ownerId)),
            ],
            'role' => ['required', Rule::in(['admin', 'member'])],
        ]);

        $invitee = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                // random placeholder password until the invitee sets their own
                'password' => Hash::make(Str::random(40)),
                'company' => '',
            ]
        );

        // ensure name is populated for existing users
        if (!$invitee->name && $validated['name']) {
            $invitee->forceFill(['name' => $validated['name']])->save();
        }

        $token = Str::random(40);

        $invitation = TeamMember::create([
            'owner_id' => $ownerId,
            'invited_by' => $actor->id,
            'user_id' => $invitee->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => 'pending',
            'invitation_token' => $token,
            'accepted_at' => null,
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation, $this->buildAcceptUrl($invitation)));

        return response()->json([
            'message' => 'Invitation sent.',
            'invitation' => $invitation,
        ], 201);
    }

    protected function buildAcceptUrl(TeamMember $invitation): string
    {
        return route('invitations.accept', ['token' => $invitation->invitation_token]);
    }

    public function showInvitation(string $token)
    {
        $invitation = TeamMember::where('invitation_token', $token)->firstOrFail();

        if ($invitation->status === 'accepted') {
            return response()->json(['message' => 'Invitation already accepted'], 410);
        }

        return [
            'name' => $invitation->name,
            'email' => $invitation->email,
            'role' => $invitation->role,
            'owner' => optional($invitation->owner)->only(['id', 'name', 'email']),
        ];
    }

    public function accept(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $invitation = TeamMember::where('invitation_token', $validated['token'])->firstOrFail();

        if ($invitation->status === 'accepted') {
            return response()->json(['message' => 'Invitation already accepted'], 422);
        }

        $name = $validated['name'] ?? $invitation->name;

        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'company' => '',
            ]);
        } else {
            // update name/password on acceptance
            $user->forceFill([
                'name' => $name,
                'password' => Hash::make($validated['password']),
            ])->save();
        }

        $invitation->update([
            'user_id' => $user->id,
            'name' => $name,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'You have joined the team.',
        ]);
    }

    public function destroy(Request $request, TeamMember $member)
    {
        $user = $request->user();
        $ownerId = $user->teamOwnerId();

        if ($member->owner_id !== $ownerId) {
            return response()->json(['message' => 'You are not authorized to remove this member.'], 403);
        }

        if ($member->status === 'accepted' && $member->user_id === $user->id) {
            return response()->json(['message' => 'You cannot remove yourself as the owner.'], 422);
        }

        $member->delete();

        return response()->json(['message' => 'Team member removed.']);
    }

    private function inviteLimitFor(User $user): ?int
    {
        $plan = $this->planForUser($user);

        return $plan['team_limit'] ?? 0;
    }

    private function planForUser(User $user): array
    {
        $plans = collect(config('subscriptions.plans', []));
        if ($plans->isEmpty()) {
            return [];
        }

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');
        if ($subscription && $subscription->stripe_price) {
            $matched = $plans->first(fn ($plan) => ($plan['price_id'] ?? null) === $subscription->stripe_price);
            if ($matched) {
                return $matched;
            }
        }

        $defaultKey = config('subscriptions.default_plan');
        if ($defaultKey && $plans->has($defaultKey)) {
            return $plans->get($defaultKey);
        }

        $starter = $plans->firstWhere('name', 'Starter');
        return $starter ?? $plans->first();
    }
}
