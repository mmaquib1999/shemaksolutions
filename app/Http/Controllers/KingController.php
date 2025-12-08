<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

class KingController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:8000',
            'provider' => 'nullable|string',
            'compression_level' => 'nullable|integer'
        ]);

        $message = $request->message;
        $provider = $request->provider ?? 'grok';
        $compressionLevel = $request->compression_level ?? 1;

        $isEmergency = $this->detectEmergency($message);

        // Load real framework
        $framework = $this->loadFramework($compressionLevel);
        if ($isEmergency) {
            $framework .= "\nEMERGENCY PROTOCOL #21 ACTIVATED\n";
        }

        // Call provider
        try {
            $response = $this->sendToLLM($provider, $message, $framework);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'timestamp' => now()->timestamp
            ], 500);
        }

        return response()->json([
            'success' => true,
            'content' => $response['content'],
            'model'   => $response['model'],
            'provider' => $provider,
            'emergency_detected' => $isEmergency,
            'timestamp' => now()->timestamp,
        ]);
    }

    private function detectEmergency($message)
    {
        $keywords = [
            'emergency','accident','injury','hurt','bleeding',
            'unconscious','not breathing','fire','explosion',
            'trapped','crushed','electrocuted','seizure','help','911'
        ];

        $lower = strtolower($message);

        foreach ($keywords as $word) {
            if (str_contains($lower, $word)) {
                return true;
            }
        }

        return false;
    }

    private function loadFramework($level)
    {
        return "K.I.N.G FRAMEWORK v17.0 — Loaded Protocols — Level $level\n"
             . "Mission: Zero Deaths\n"
             . "Protocols: 10,001\n"
             . "TIER 0 → TIER 9 active\n";
    }

    private function sendToLLM($provider, $message, $framework)
    {
        $config = [
            'grok' => [
                'url' => 'https://api.x.ai/v1/chat/completions',
                'key' => env('GROK_API_KEY'),
                'model' => 'grok-4.1'
            ]
        ];

        if (!isset($config[$provider])) {
            throw new Exception("Unknown provider: $provider");
        }

        $providerConfig = $config[$provider];

        $payload = [
            'model' => $providerConfig['model'],
            'messages' => [
                ['role' => 'system', 'content' => $framework],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2048
        ];

        $ch = curl_init($providerConfig['url']);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: "Bearer '.$providerConfig['key'].'"',
            ]
        ]);

        $result = curl_exec($ch);
        $data   = json_decode($result, true);

        if (!$result || !isset($data['choices'][0]['message']['content'])) {
            throw new Exception("Invalid LLM response: ".$result);
        }

        return [
            'content' => $data['choices'][0]['message']['content'],
            'model'   => $providerConfig['model']
        ];
    }
}
