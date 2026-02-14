<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Anthropic Claude Vendor Implementation
 * Handles all Anthropic API interactions
 */
class AnthropicVendor implements AiVendorInterface
{
    /**
     * Anthropic API key
     */
    protected string $apiKey;

    /**
     * API base URL
     */
    protected string $baseUrl;

    /**
     * Default model to use
     */
    protected string $model;

    /**
     * Anthropic API version
     */
    protected string $apiVersion = '2023-06-01';

    /**
     * Last API response
     */
    protected ?array $lastResponse = null;

    /**
     * Last error message
     */
    protected ?string $lastError = null;

    /**
     * Create a new AnthropicVendor instance
     */
    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? config('shopify-enhanced.brain.providers.anthropic.api_key');
        $this->baseUrl = $baseUrl ?? config('shopify-enhanced.brain.providers.anthropic.base_url', 'https://api.anthropic.com/v1');
        $this->model = $model ?? config('shopify-enhanced.brain.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
    }

    /**
     * @inheritDoc
     */
    public function chat(array $messages, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Anthropic API key is not configured.');
        }

        // Extract system prompt from messages
        $systemPrompt = null;
        $formattedMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemPrompt = $message['content'];
            } else {
                $formattedMessages[] = [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ];
            }
        }

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $formattedMessages,
            'max_tokens' => $options['max_tokens'] ?? 1000,
        ];

        // Add optional parameters
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['top_p'])) {
            $payload['top_p'] = $options['top_p'];
        }
        if (isset($options['top_k'])) {
            $payload['top_k'] = $options['top_k'];
        }
        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
        ])
            ->acceptJson()
            ->timeout(60)
            ->post("{$this->baseUrl}/messages", $payload);

        if (!$response->successful()) {
            $this->lastError = $response->body();
            Log::error('Anthropic API error', [
                'status' => $response->status(),
                'body' => $this->lastError,
            ]);
            throw new \Exception("Anthropic API request failed: {$this->lastError}");
        }

        $this->lastResponse = $response->json();
        $this->lastError = null;

        return $this->lastResponse['content'][0]['text'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function stream(array $messages, callable $callback, array $options = []): void
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Anthropic API key is not configured.');
        }

        // For now, call non-streaming and pass result to callback
        // Full streaming implementation would require curl or a streaming HTTP client
        $result = $this->chat($messages, $options);
        $callback($result);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'anthropic';
    }

    /**
     * @inheritDoc
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * @inheritDoc
     */
    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    /**
     * @inheritDoc
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Set a custom model
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get available Claude models
     */
    public static function getAvailableModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Most balanced)',
            'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Fastest)',
            'claude-3-opus-20240229' => 'Claude 3 Opus (Most capable)',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet (Balanced)',
        ];
    }
}
