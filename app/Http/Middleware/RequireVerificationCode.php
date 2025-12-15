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

        // Already verified with code
        if ($user->verification_verified_at) {
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
            return response()->json(['message' => 'verification_required'], 403);
        }

        return redirect('/verify-code');
    }
}
