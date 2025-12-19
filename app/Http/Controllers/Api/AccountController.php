<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'company' => $user->company,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'name' => $validated['name'],
            'company' => $validated['company'] ?? '',
        ])->save();

        return response()->json([
            'message' => 'Account updated.',
            'name' => $user->name,
            'email' => $user->email, // not changed
            'company' => $user->company,
        ]);
    }

    /**
     * Soft-delete the account: invalidate verification and log out.
     * We do not remove the user record; verification will be required on next login.
     */
    public function softDelete(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->forceFill([
            'email_verified_at' => null,
            'verification_verified_at' => null,
            'verification_code' => null,
            'verification_expires_at' => null,
        ])->save();

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Account flagged for deletion. You have been logged out and will need to verify email to access again.',
        ]);
    }
}
