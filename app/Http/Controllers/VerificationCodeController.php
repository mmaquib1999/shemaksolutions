<?php

namespace App\Http\Controllers;

use App\Notifications\SendVerificationCode;
use Illuminate\Http\Request;

class VerificationCodeController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->verification_code || !$user->verification_expires_at) {
            return response()->json(['message' => 'No verification code found'], 422);
        }

        if ($user->verification_expires_at->isPast()) {
            return response()->json(['message' => 'Code expired'], 422);
        }

        if ($user->verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $user->forceFill([
            'verification_code' => null,
            'verification_expires_at' => null,
            'verification_verified_at' => now(),
            // Optional: also mark primary email verified for consistency
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return response()->json(['ok' => true]);
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $code = $this->generateCode();
        $user->forceFill([
            'verification_code' => $code,
            'verification_expires_at' => now()->addMinutes(15),
        ])->save();

        $user->notify(new SendVerificationCode($code));

        return response()->json(['ok' => true]);
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
