<?php

namespace App\Services;

use App\Models\AiProviderKey;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Laravel-native version of the King Integrated v17 logic.
 * - Mirrors provider configuration/payloads from king_integrated_v17.php
 * - Uses ai_provider_keys to pick the active default (or requested) provider/model/api key
 */
class KingIntegratedService
{
    public function ask(int $userId, string $message, ?string $provider = null, ?string $model = null, int $compressionLevel = 1): array
    {
        $framework = (new KingFramework())->loadFramework($compressionLevel);
        $detector = new EmergencyDetector();
        $isEmergency = $detector->detect($message);

        $key = $this->resolveKey($userId, $provider);
        if (!$key) {
            throw new Exception('Please add an AI provider key before asking K.I.N.G.', 400);
        }

        $providerSlug = $provider ?? $key->provider;
        $providerConfig = $this->buildProviderConfig($providerSlug, $key, $model);

        $client = new LLMProvider($providerSlug, $providerConfig);
        $llmResponse = $client->sendRequest($message, $framework);

        $key->increment('total_queries');

        return [
            'success' => true,
            'content' => $llmResponse['content'],
            'model' => $llmResponse['model'],
            'provider' => $llmResponse['provider'],
            'provider_display' => $key->name,
            'emergency_detected' => $isEmergency,
            'framework' => [
                'version' => KingConfig::API_VERSION,
                'protocols' => KingConfig::PROTOCOL_COUNT,
                'compression_level' => $compressionLevel,
            ],
            'usage' => $llmResponse['usage'] ?? null,
            'total_queries' => $key->total_queries,
        ];
    }

    private function resolveKey(int $userId, ?string $provider): ?AiProviderKey
    {
        if ($provider) {
            $match = AiProviderKey::where('user_id', $userId)
                ->where('provider', $provider)
                ->orderByDesc('is_default')
                ->first();

            if ($match) {
                return $match;
            }
        }

        return AiProviderKey::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->first();
    }

    private function buildProviderConfig(string $provider, AiProviderKey $key, ?string $modelOverride): array
    {
        $base = KingConfig::getLLMProvider($provider);

        return array_merge($base, [
            'api_key' => $key->api_key,
            'model' => $modelOverride ?: $key->model ?: ($base['model'] ?? null),
        ]);
    }
}

class KingConfig
{
    public const API_VERSION = '17.0';
    public const PROTOCOL_COUNT = 10001;

    private static array $providers = [
        'grok' => [
            'name' => 'xAI Grok-4.1 (Fast Reasoning)',
            'api_url' => 'https://api.x.ai/v1/chat/completions',
            'model' => 'grok-4.1',
            'max_tokens' => 8192,
            'temperature' => 0.7,
            'enabled' => true,
            'supports_reasoning' => true,
            'supports_vision' => true,
            'context_window' => 128000,
            'reasoning_mode' => 'fast',
        ],
        'xai' => [
            'name' => 'xAI Grok-4.1 (Fast Reasoning)',
            'api_url' => 'https://api.x.ai/v1/chat/completions',
            'model' => 'grok-4.1',
            'max_tokens' => 8192,
            'temperature' => 0.7,
            'enabled' => true,
            'supports_reasoning' => true,
            'supports_vision' => true,
            'context_window' => 128000,
            'reasoning_mode' => 'fast',
        ],
        'claude' => [
            'name' => 'Anthropic Claude',
            'api_url' => 'https://api.anthropic.com/v1/messages',
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'api_url' => 'https://api.anthropic.com/v1/messages',
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
        'openai' => [
            'name' => 'OpenAI GPT',
            'api_url' => 'https://api.openai.com/v1/chat/completions',
            'model' => 'gpt-4o',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'model' => 'gemini-1.5-pro',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
        'google' => [
            'name' => 'Google Gemini',
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'model' => 'gemini-1.5-pro',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'api_url' => 'https://api.deepseek.com/v1/chat/completions',
            'model' => 'deepseek-chat',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false,
        ],
    ];

    public static function getLLMProvider(string $provider = 'grok'): array
    {
        return self::$providers[$provider] ?? self::$providers['grok'];
    }
}

class KingFramework
{
    public function loadFramework(int $compressionLevel = 1): string
    {
        $compressionLevel = max(0, min(3, $compressionLevel));

        $framework = $this->getHeader();
        $framework .= $this->getMission();
        $framework .= $this->getSystemDirectives();
        $framework .= $this->loadTier0();
        $framework .= $this->loadTier1();
        $framework .= $this->loadTier2();
        $framework .= $this->loadTier3();
        $framework .= $this->loadTier4();
        $framework .= $this->loadTier5();
        $framework .= $this->loadTier6();
        $framework .= $this->loadTier7();
        $framework .= $this->loadTier8();
        $framework .= $this->loadTier9();
        $framework .= $this->compressionNotes($compressionLevel);

        return $framework;
    }

    private function getHeader(): string
    {
        return "CopiousK.I.N.G. Framework v17.0 | Protocols: 10,001\n";
    }

    private function getMission(): string
    {
        return <<<TXT
MISSION: Training Abundant | Training Simple | Zero Deaths
You are K.I.N.G. - an AI workplace safety intelligence system.
- 10,001 operational safety protocols across 9 tiers
- Multi-provider: Grok-4.1 Fast Reasoning, Claude, GPT, Gemini, DeepSeek, Custom
- BYOLLM architecture with quantum compression support
- Mission: zero workplace deaths via abundant, accessible guidance

TXT;
    }

    private function getSystemDirectives(): string
    {
        return <<<TXT
CRITICAL OPERATING PRINCIPLES:
- Safety first: never compromise safety for cost or speed
- Voice: professional, confident, action-oriented safety expert
- No hedging: avoid "I think", "maybe", "possibly", "I'm just an AI"
- Response structure
  * Emergency: ACTION -> EXPLANATION -> VERIFICATION
  * Training: CONCEPT -> EXAMPLE -> APPLICATION
  * Compliance: REQUIREMENT -> RATIONALE -> IMPLEMENTATION
- Emergency protocols: auto-detect danger terms, activate Protocol #21, call emergency services, prioritize scene safety

TXT;
    }

    private function loadTier0(): string
    {
        return <<<TXT
TIER 0: Foundation Protocols (0-100)
- Protocol #0: System initialization when activated (hello/activate/=0)
  Response: "K.I.N.G. ACTIVATED | Version 17.0 | Protocols 10,001 | Fast Reasoning ready"
- Protocol #1: Mission alignment (always active) - worker safety supersedes all else
- Protocol #2: Voice consistency (always active) - confident, clear, action-first
- Protocol #3: Hazard identification (physical, chemical, biological, ergonomic, psychosocial, environmental)
- Protocol #4: Risk assessment matrix (probability x severity, 1-25)
- Protocol #5: Hierarchy of controls (Eliminate -> Substitute -> Engineering -> Administrative -> PPE last)
- Protocol #21: Emergency response triggers (emergency, fire, electrocution, trapped, 911, mayday, etc.)

TXT;
    }

    private function loadTier1(): string
    {
        return "[TIER 1: Core Operations (101-1000)]\n";
    }

    private function loadTier2(): string
    {
        return "[TIER 2: Rapid Deployment (1001-2000)]\n";
    }

    private function loadTier3(): string
    {
        return "[TIER 3: Advanced Operations (2001-3000)]\n";
    }

    private function loadTier4(): string
    {
        return "[TIER 4: Predictive Intelligence (3001-5000)]\n";
    }

    private function loadTier5(): string
    {
        return "[TIER 5: AI Optimization (5001-6000)]\n";
    }

    private function loadTier6(): string
    {
        return "[TIER 6: Enterprise Integration (6001-8000)]\n";
    }

    private function loadTier7(): string
    {
        return "[TIER 7: Economic Systems (8001-9000)]\n";
    }

    private function loadTier8(): string
    {
        return "[TIER 8: Quantum Intelligence (9001-10000)]\n";
    }

    private function loadTier9(): string
    {
        return "[TIER 9: Cloud Orchestration (10001)]\n";
    }

    private function compressionNotes(int $level): string
    {
        $notes = [
            0 => "Mode: VERBOSE - include rationale and references.\n",
            1 => "Mode: STRUCTURED - concise bullets with action and rationale.\n",
            2 => "Mode: QUANTUM - maximize compression, keep directives intact.\n",
            3 => "Mode: PREDICTIVE - anticipate follow-ups; stay concise.\n",
        ];

        return $notes[$level] ?? $notes[1];
    }
}

class EmergencyDetector
{
    private array $keywords = [
        'emergency', 'accident', 'injury', 'hurt', 'pain',
        'bleeding', 'unconscious', 'not breathing', 'chest pain',
        'heart attack', 'stroke', 'seizure', 'choking',
        'fall', 'fell', 'dropped', 'crushed', 'trapped',
        'struck', 'hit', 'cut', 'burn', 'burned',
        'electrocuted', 'shocked', 'fire', 'explosion',
        'smoke', 'flames', 'gas leak', 'spill', 'collapse',
        'help', 'mayday', 'sos', '911', 'man down',
    ];

    public function detect(string $message): bool
    {
        $lower = strtolower($message);
        foreach ($this->keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }
}

class LLMProvider
{
    private string $provider;
    private array $config;

    public function __construct(string $provider, array $config)
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    public function sendRequest(string $message, string $framework): array
    {
        Log::info('K.I.N.G. sending request', [
            'provider' => $this->provider,
            'message_length' => strlen($message),
            'framework_length' => strlen($framework),
        ]);

        $payload = $this->buildPayload($message, $framework);
        $response = $this->executeCurl($payload);

        return $this->parseResponse($response);
    }

    private function buildPayload(string $message, string $framework): string
    {
        if (in_array($this->provider, ['grok', 'xai'], true)) {
            $payload = [
                'model' => $this->config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $framework],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => $this->config['temperature'],
                'max_tokens' => $this->config['max_tokens'],
            ];

            if (!empty($this->config['supports_reasoning'])) {
                $payload['reasoning'] = [
                    'enabled' => true,
                    'mode' => $this->config['reasoning_mode'] ?? 'fast',
                ];
            }

            return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (in_array($this->provider, ['gemini', 'google'], true)) {
            return json_encode([
                'contents' => [
                    ['parts' => [['text' => $framework . "\n\n" . $message]]],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (in_array($this->provider, ['claude', 'anthropic'], true)) {
            return json_encode([
                'model' => $this->config['model'],
                'max_tokens' => $this->config['max_tokens'],
                'temperature' => $this->config['temperature'],
                'messages' => [
                    ['role' => 'user', 'content' => $framework . "\n\n" . $message],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode([
            'model' => $this->config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $framework],
                ['role' => 'user', 'content' => $message],
            ],
            'temperature' => $this->config['temperature'],
            'max_tokens' => $this->config['max_tokens'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function executeCurl(string $payload): string
    {
        $url = $this->config['api_url'];

        if (in_array($this->provider, ['gemini', 'google'], true)) {
            $url = rtrim($url, '/') . '/' . ($this->config['model'] ?? 'gemini-1.5-pro') . ':generateContent?key=' . $this->config['api_key'];
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $this->headers(),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FAILONERROR => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);

        curl_close($ch);

        Log::debug('K.I.N.G. API response', [
            'provider' => $this->provider,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'curl_errno' => $curlErrno,
            'response_preview' => is_string($response) ? substr($response, 0, 500) : null,
        ]);

        if ($curlErrno !== 0) {
            throw new Exception("CURL Error: $curlError", $curlErrno);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode((string) $response, true);
            $errorMsg = $errorData['error']['message'] ?? $errorData['error'] ?? 'Unknown API error';
            throw new Exception("API Error ($httpCode): $errorMsg", $httpCode);
        }

        return (string) $response;
    }

    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }

        if (isset($data['choices'][0]['message']['content'])) {
            return [
                'content' => $data['choices'][0]['message']['content'],
                'model' => $data['model'] ?? $this->config['model'],
                'usage' => $data['usage'] ?? null,
                'provider' => $this->provider,
            ];
        }

        if (isset($data['content'][0]['text'])) {
            return [
                'content' => $data['content'][0]['text'],
                'model' => $data['model'] ?? $this->config['model'],
                'provider' => $this->provider,
            ];
        }

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'content' => $data['candidates'][0]['content']['parts'][0]['text'],
                'model' => $data['model'] ?? $this->config['model'],
                'provider' => $this->provider,
            ];
        }

        throw new Exception('Unexpected API response structure');
    }

    private function headers(): array
    {
        if (in_array($this->provider, ['claude', 'anthropic'], true)) {
            return [
                'x-api-key: ' . $this->config['api_key'],
                'Content-Type: application/json',
            ];
        }

        if (in_array($this->provider, ['gemini', 'google'], true)) {
            return [
                'Content-Type: application/json',
            ];
        }

        return [
            'Authorization: Bearer ' . $this->config['api_key'],
            'Content-Type: application/json',
        ];
    }
}
