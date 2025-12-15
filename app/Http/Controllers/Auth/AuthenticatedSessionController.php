<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // If user is already verified, normalize verification fields to prevent re-prompts
        $user = $request->user();
        if ($user) {
            if ($user->email_verified_at || $user->verification_verified_at) {
                $user->forceFill([
                    'verification_code' => null,
                    'verification_expires_at' => null,
                    'verification_verified_at' => $user->verification_verified_at ?? $user->email_verified_at ?? now(),
                    'email_verified_at' => $user->email_verified_at ?? $user->verification_verified_at ?? now(),
                ])->save();
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
