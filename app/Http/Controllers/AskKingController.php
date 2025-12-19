<?php

namespace App\Http\Controllers;

use App\Models\AiProviderKey;
use App\Services\KingIntegratedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AskKingController extends Controller
{
    public function ask(Request $request, KingIntegratedService $king)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:8000',
            'compression_level' => ['sometimes', 'integer', 'min:0', 'max:3'],
        ]);

        $user = $request->user();
        $ownerId = $user->teamOwnerId();

        // Provider/model are not sent from Vue; fetch the user's default/first ai_provider_key
        $key = AiProviderKey::where('user_id', $ownerId)
            ->orderByDesc('is_default')
            ->first();

        if (!$key) {
            return response()->json([
                'success' => false,
                'error' => 'Please add an AI provider key before asking K.I.N.G.',
            ], 400);
        }

        $provider = $key->provider;
        $model = $key->model;
        $compression = (int) ($validated['compression_level'] ?? 1);
        $message = trim($validated['message']);

        // Delegate to the King Integrated service (keeping its logic untouched)
        try {
            $result = $king->ask($ownerId, $message, $provider, $model, $compression);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }

        return $result;
    }
}
