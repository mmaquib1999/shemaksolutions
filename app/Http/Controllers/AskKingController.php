<?php

namespace App\Http\Controllers;

use App\Models\AiProviderKey;
use App\Services\AIRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AskKingController extends Controller
{
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:8000',
            'provider' => ['sometimes', 'string', Rule::in(['grok', 'openai', 'claude', 'gemini', 'deepseek'])],
            'model' => ['sometimes', 'string', 'max:255'],
            'compression_level' => ['sometimes', 'integer', 'min:0', 'max:3'],
        ]);

        $user = Auth::user();

        $provider = $validated['provider'] ?? 'grok';
        $compression = (int) ($validated['compression_level'] ?? 1);
        $message = trim($validated['message']);

        // Prefer a key matching the requested provider; fall back to user's default/first key.
        $key = AiProviderKey::where('user_id', $user->id)
            ->where('provider', $provider)
            ->orderByDesc('is_default')
            ->first();

        if (!$key) {
            $key = AiProviderKey::where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->first();

            if ($key && empty($validated['provider'])) {
                $provider = $key->provider;
            }
        }

        if (!$key) {
            return response()->json([
                'success' => false,
                'error' => 'Please add an AI provider key before asking K.I.N.G.',
            ], 400);
        }

        $apiKey = $key->api_key; // decrypted via accessor
        $model = $validated['model'] ?? $key->model;

        $service = new AIRequestService();

        // KING Framework injected as system message (aligned with v17 logic)
        $framework = $this->buildFramework($compression);
        $isEmergency = $this->detectEmergency($message);

        try {
            $result = $service->send(
                $provider,
                $model,
                $apiKey,
                $message,
                $framework
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }

        // Update query usage count
        $key->increment('total_queries');

        return [
            'success' => true,
            'content' => $result['text'] ?? '',
            'provider' => $result['provider'] ?? $provider,
            'model' => $result['model'] ?? $model,
            'total_queries' => $key->total_queries,
            'emergency_detected' => $isEmergency,
        ];
    }

    private function buildFramework(int $compressionLevel = 1): string
    {
        $compressionLevel = max(0, min(3, $compressionLevel));

        $header = "CopiousK.I.N.G. Framework v17.0 | Protocols: 10,001 | Mission: Training Abundant | Training Simple | Zero Deaths\n";
        $mission = "You are K.I.N.G., an AI workplace safety system. Safety overrides speed or cost. Always provide clear, actionable steps.\n";
        $voice = "Voice: Professional safety expert. Avoid hedging ('I think', 'maybe'). Be direct and confident.\n";
        $hierarchy = "Hierarchy of Controls: 1) Eliminate 2) Substitute 3) Engineering 4) Administrative 5) PPE (last resort).\n";
        $emergency = "If emergency or high-risk terms detected: Activate Protocol #21. First: call emergency services, ensure scene safety, do not move victim unless necessary.\n";

        $compressionNotes = [
            0 => "Mode: VERBOSE - include rationale and references.",
            1 => "Mode: STRUCTURED - concise bullets with action and rationale.",
            2 => "Mode: QUANTUM - maximize compression, keep directives intact.",
            3 => "Mode: PREDICTIVE - anticipate follow-ups; stay concise.",
        ];

        return $header
            . $mission
            . $voice
            . $hierarchy
            . $emergency
            . ($compressionNotes[$compressionLevel] ?? $compressionNotes[1]);
    }

    private function detectEmergency(string $message): bool
    {
        $keywords = [
            'emergency', 'accident', 'injury', 'hurt', 'pain',
            'bleeding', 'unconscious', 'not breathing', 'chest pain',
            'heart attack', 'stroke', 'seizure', 'choking',
            'fall', 'fell', 'dropped', 'crushed', 'trapped',
            'struck', 'hit', 'cut', 'burn', 'burned',
            'electrocuted', 'shocked', 'fire', 'explosion',
            'smoke', 'flames', 'gas leak', 'spill', 'collapse',
            'help', 'mayday', 'sos', '911', 'man down',
        ];

        $lower = strtolower($message);
        foreach ($keywords as $word) {
            if (strpos($lower, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}
