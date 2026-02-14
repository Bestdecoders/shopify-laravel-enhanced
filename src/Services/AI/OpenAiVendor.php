<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI Vendor Implementation
 * Handles all OpenAI API interactions
 */
class OpenAiVendor implements AiVendorInterface
{
    /**
     * OpenAI API key
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
     * Last API response
     */
    protected ?array $lastResponse = null;

    /**
     * Last error message
     */
    protected ?string $lastError = null;

    /**
     * Create a new OpenAiVendor instance
     */
    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? config('shopify-enhanced.brain.providers.openai.api_key');
        $this->baseUrl = $baseUrl ?? config('shopify-enhanced.brain.providers.openai.base_url', 'https://api.openai.com/v1');
        $this->model = $model ?? config('shopify-enhanced.brain.providers.openai.model', 'gpt-4o-mini');
    }

    /**
     * @inheritDoc
     */
    public function chat(array $messages, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('OpenAI API key is not configured.');
        }

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
        ];

        // Add optional parameters
        if (isset($options['top_p'])) {
            $payload['top_p'] = $options['top_p'];
        }
        if (isset($options['frequency_penalty'])) {
            $payload['frequency_penalty'] = $options['frequency_penalty'];
        }
        if (isset($options['presence_penalty'])) {
            $payload['presence_penalty'] = $options['presence_penalty'];
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            $this->lastError = $response->body();
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $this->lastError,
            ]);
            throw new \Exception("OpenAI API request failed: {$this->lastError}");
        }

        $this->lastResponse = $response->json();
        $this->lastError = null;

        return $this->lastResponse['choices'][0]['message']['content'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function stream(array $messages, callable $callback, array $options = []): void
    {
        if (!$this->isConfigured()) {
            throw new \Exception('OpenAI API key is not configured.');
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
        return 'openai';
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
     * Get available models
     */
    public static function getAvailableModels(): array
    {
        return [
            'gpt-4o' => 'GPT-4 Omni (Most capable)',
            'gpt-4o-mini' => 'GPT-4 Omni Mini (Fast, cost-effective)',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Legacy)',
        ];
    }

    /**
     * List all available models from OpenAI API
     */
    public function listModels(): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('OpenAI API key is not configured.');
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/models");

        if (!$response->successful()) {
            throw new \Exception("Failed to list models: {$response->body()}");
        }

        return $response->json()['data'] ?? [];
    }
}
