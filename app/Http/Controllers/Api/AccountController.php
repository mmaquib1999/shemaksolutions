<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
