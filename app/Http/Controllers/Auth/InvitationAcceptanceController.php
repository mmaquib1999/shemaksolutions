<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationAcceptanceController extends Controller
{
    public function create(string $token)
    {
        $invitation = TeamMember::where('invitation_token', $token)->firstOrFail();

        if ($invitation->status === 'accepted') {
            return redirect()->route('login')->with('status', 'Invitation already accepted. Please log in.');
        }

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $invitation = TeamMember::where('invitation_token', $validated['token'])->firstOrFail();

        if ($invitation->status === 'accepted') {
            return redirect()->route('login')->with('status', 'Invitation already accepted. Please log in.');
        }

        $name = $validated['name'] ?: $invitation->name;

        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'company' => '',
            ]);
        } else {
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

        return redirect()->route('login')->with('status', 'Password set. You can log in.');
    }
}
