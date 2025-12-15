<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVerificationCode
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // If email is verified or verification timestamp exists, allow through and clear stale codes
        if ($user->email_verified_at || $user->verification_verified_at) {
            if ($user->verification_code !== null || $user->verification_expires_at !== null) {
                $user->forceFill([
                    'verification_code' => null,
                    'verification_expires_at' => null,
                    'verification_verified_at' => $user->verification_verified_at ?? $user->email_verified_at ?? now(),
                    'email_verified_at' => $user->email_verified_at ?? $user->verification_verified_at ?? now(),
                ])->save();
            }
            return $next($request);
        }

        // Allow verification/resend/logout routes
        $allowedPaths = [
            '/verify-code',
            '/logout',
            '/api/verify-code',
            '/api/verify-code/resend',
        ];

        foreach ($allowedPaths as $path) {
            if ($request->is(ltrim($path, '/')) || $request->is(ltrim($path, '/').'*')) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'verification_required', 'verified' => false], 403);
        }

        return redirect('/verify-code');
    }
}
