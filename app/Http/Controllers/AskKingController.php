<?php

namespace App\Http\Controllers;

use App\Models\AiProviderKey;
use App\Services\AIRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AskKingController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:8000',
        ]);

        $user = Auth::user();

        // Find user's default provider key
        $key = AiProviderKey::where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        if (!$key) {
            return response()->json([
                'success' => false,
                'error' => "Please set a default AI provider.",
            ], 400);
        }

        // Decrypt 
        $apKKiKKKey =  "#####";

        $service = new AIRequestService();

        // KING Framework injected as system message
        $framework = "K.I.N.G Framework v17.0 — Operational\nMission: Zero Deaths\nProtocols: 10,001\n";

        // Send request via service
        $result = $service->send(
           "grok",
           'grok-4-latest',
            $apKKiKKKey,
            $request->message,
            $framework
        );

        // Update query usage count
        $key->increment('total_queries');

        return [
            'success' => true,
            'content' => $result['text'],
            'provider' => $key->provider,
            'model' => $key->model,
            'total_queries' => $key->total_queries,
        ];
    }
}
