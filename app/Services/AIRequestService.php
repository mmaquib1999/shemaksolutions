<?php

namespace App\Services;

use Exception;

class AIRequestService
{
    /**
     * EXACT SAME PROVIDER ARRAY AS K.I.N.G.
     */
    private $providers = [
        'grok' => [
            'name' => 'xAI Grok-4.1 (Fast Reasoning)',
            'api_url' => 'https://api.x.ai/v1/chat/completions',
            'api_key' => '',
            'model' => 'grok-4.1',
            'max_tokens' => 8192,
            'temperature' => 0.7,
            'enabled' => true,
            'supports_reasoning' => true,
            'supports_vision' => true,
            'context_window' => 128000,
            'reasoning_mode' => 'fast'
        ],

        'claude' => [
            'name' => 'Anthropic Claude',
            'api_url' => 'https://api.anthropic.com/v1/messages',
            'api_key' => '',
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false
        ],

        'openai' => [
            'name' => 'OpenAI GPT',
            'api_url' => 'https://api.openai.com/v1/chat/completions',
            'api_key' => '',
            'model' => 'gpt-4o',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false
        ],

        'gemini' => [
            'name' => 'Google Gemini',
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'api_key' => '',
            'model' => 'gemini-1.5-pro',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false
        ],

        'deepseek' => [
            'name' => 'DeepSeek',
            'api_url' => 'https://api.deepseek.com/v1/chat/completions',
            'api_key' => '',
            'model' => 'deepseek-chat',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'enabled' => false
        ]
    ];

    /**
     * MAIN API METHOD
     */
    public function send($provider, $model, $apiKey, $message, $framework)
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("AI Provider not supported: $provider");
        }

        $p = $this->providers[$provider];

        // Allow override API key from DB or fallback to static config
        $apiKey = $apiKey ?: $p['api_key'];
        if (!$apiKey) {
            throw new Exception("No API key available for provider: $provider");
        }

        $url = $p['api_url'];

        // BUILD PAYLOAD BASED ON PROVIDER TYPE
        $payload = $this->buildPayload($provider, $model, $message, $framework, $p);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->getHeaders($provider, $apiKey),
        ]);

        $raw = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($raw, true);

        return $this->extractText($provider, $data, $raw);
    }

    /**
     * BUILD PAYLOAD PER PROVIDER
     */
    private function buildPayload($provider, $model, $message, $framework, $p)
    {
        switch ($provider) {
            case 'grok':
            case 'openai':
            case 'deepseek':
                return [
                    'model' => $model ?: $p['model'],
                    'messages' => [
                        ['role' => 'system', 'content' => $framework],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'temperature' => $p['temperature'],
                    'max_tokens' => $p['max_tokens'],
                ];

            case 'claude':
                return [
                    "model" => $model ?: $p['model'],
                    "max_tokens" => $p['max_tokens'],
                    "temperature" => $p['temperature'],
                    "messages" => [
                        ["role" => "user", "content" => $framework . "\n\n" . $message]
                    ]
                ];

            case 'gemini':
                return [
                    "contents" => [
                        ["parts" => ["text" => $framework . "\n\n" . $message]]
                    ]
                ];
        }

        throw new Exception("Unknown provider payload type.");
    }

    /**
     * HEADERS FOR PROVIDER
     */
    private function getHeaders($provider, $apiKey)
    {
        if ($provider === 'claude') {
            return [
                "x-api-key: $apiKey",
                "Content-Type: application/json",
            ];
        }

        if ($provider === 'gemini') {
            return [
                "Content-Type: application/json",
            ];
        }

        // OpenAI-style
        return [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
        ];
    }

    /**
     * EXTRACT TEXT BASED ON PROVIDER RESPONSE FORMAT
     */
    private function extractText($provider, $data, $raw)
    {
        if ($provider === 'claude' && isset($data['content'][0]['text'])) {
            return ['text' => $data['content'][0]['text']];
        }

        if ($provider === 'gemini' && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['text' => $data['candidates'][0]['content']['parts'][0]['text']];
        }

        if (isset($data['choices'][0]['message']['content'])) {
            return ['text' => $data['choices'][0]['message']['content']];
        }

        throw new Exception("Invalid provider response: " . $raw);
    }
}
