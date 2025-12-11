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

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $memberUserIds = TeamMember::where('owner_id', $user->id)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        $userIds = collect([$user->id])
            ->concat($memberUserIds)
            ->unique()
            ->values();

        $queriesByUser = AiProviderKey::whereIn('user_id', $userIds)
            ->selectRaw('user_id, COALESCE(SUM(total_queries), 0) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $invitations = TeamMember::where('owner_id', $user->id)
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
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'owner',
            'status' => 'active',
            'is_owner' => true,
            'total_queries' => (int) ($queriesByUser[$user->id] ?? 0),
            'accepted_at' => $user->email_verified_at,
        ]])->concat($invitations)->values();

        return response()->json([
            'seat_limit' => 10,
            'members' => $members,
        ]);
    }

    public function invite(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::notIn([$user->email]),
                Rule::unique('team_members', 'email')->where(fn ($q) => $q->where('owner_id', $user->id)),
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
            'owner_id' => $user->id,
            'invited_by' => $user->id,
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

        if ($member->owner_id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to remove this member.'], 403);
        }

        if ($member->status === 'accepted' && $member->user_id === $user->id) {
            return response()->json(['message' => 'You cannot remove yourself as the owner.'], 422);
        }

        $member->delete();

        return response()->json(['message' => 'Team member removed.']);
    }
}
