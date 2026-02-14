<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Bestdecoders\ShopifyLaravelEnhanced\Services\AI\AiVendorInterface;
use Bestdecoders\ShopifyLaravelEnhanced\Services\AI\OpenAiVendor;
use Bestdecoders\ShopifyLaravelEnhanced\Services\AI\AnthropicVendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Brain Service - Main AI Service that uses AI vendors
 *
 * Usage:
 * useBrain('Your prompt here');
 * useBrain()->withMemory('key')->ask('Follow-up question');
 * useBrain()->returnType('json')->ask('Return JSON');
 */
class BrainService
{
    /**
     * The AI vendor instance
     */
    protected AiVendorInterface $vendor;

    /**
     * The system prompt/instructions
     */
    protected ?string $systemPrompt = null;

    /**
     * Conversation memory/history
     */
    protected array $memory = [];

    /**
     * Cache key prefix for storing memory
     */
    protected ?string $memoryKey = null;

    /**
     * Memory cache TTL in minutes (default: 24 hours)
     */
    protected int $memoryTtl = 1440;

    /**
     * Return type (text, json, array, bool, int, float)
     */
    protected string $returnType = 'text';

    /**
     * Temperature for response generation (0.0 - 2.0)
     */
    protected float $temperature = 0.7;

    /**
     * Maximum tokens in response
     */
    protected int $maxTokens = 1000;

    /**
     * Raw result from the last request
     */
    protected mixed $lastResult = null;

    /**
     * Available options to pass to vendor
     */
    protected array $vendorOptions = [];

    /**
     * Create a new BrainService instance
     */
    public function __construct(?AiVendorInterface $vendor = null)
    {
        $this->vendor = $vendor ?? $this->createDefaultVendor();
    }

    /**
     * Create the default vendor from config
     */
    protected function createDefaultVendor(): AiVendorInterface
    {
        $provider = config('shopify-enhanced.brain.default_provider', 'openai');

        return match ($provider) {
            'openai' => new OpenAiVendor(),
            'anthropic' => new AnthropicVendor(),
            default => throw new \Exception("Unsupported AI provider: {$provider}"),
        };
    }

    /**
     * Set the AI vendor
     */
    public function vendor(AiVendorInterface $vendor): self
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * Set the AI provider by name
     */
    public function provider(string $provider): self
    {
        $this->vendor = match ($provider) {
            'openai' => new OpenAiVendor(),
            'anthropic' => new AnthropicVendor(),
            default => throw new \Exception("Unsupported AI provider: {$provider}"),
        };

        return $this;
    }

    /**
     * Set the system prompt
     */
    public function systemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;
        return $this;
    }

    /**
     * Set conversation memory from array
     */
    public function withMemory(array $memory): self
    {
        $this->memory = $memory;
        return $this;
    }

    /**
     * Load conversation memory from cache
     */
    public function loadMemory(string $key, ?int $ttl = null): self
    {
        $this->memoryKey = $key;
        if ($ttl !== null) {
            $this->memoryTtl = $ttl;
        }
        $this->memory = Cache::get("brain_memory_{$key}", []);
        return $this;
    }

    /**
     * Save current memory to cache
     */
    public function saveMemory(?string $key = null): self
    {
        $cacheKey = $key ?? $this->memoryKey;
        if ($cacheKey) {
            Cache::put("brain_memory_{$cacheKey}", $this->memory, $this->memoryTtl);
            $this->memoryKey = $cacheKey;
        }
        return $this;
    }

    /**
     * Clear the conversation memory
     */
    public function clearMemory(): self
    {
        $this->memory = [];
        if ($this->memoryKey) {
            Cache::forget("brain_memory_{$this->memoryKey}");
        }
        return $this;
    }

    /**
     * Set the return type
     */
    public function returnType(string $type): self
    {
        $this->returnType = $type;
        return $this;
    }

    /**
     * Set temperature for response generation
     */
    public function temperature(float $temperature): self
    {
        $this->temperature = max(0, min(2, $temperature));
        return $this;
    }

    /**
     * Set maximum tokens
     */
    public function maxTokens(int $tokens): self
    {
        $this->maxTokens = $tokens;
        return $this;
    }

    /**
     * Set vendor-specific options
     */
    public function withOptions(array $options): self
    {
        $this->vendorOptions = array_merge($this->vendorOptions, $options);
        return $this;
    }

    /**
     * Ask a question or send a prompt
     */
    public function ask(string $prompt): mixed
    {
        // Build messages array
        $messages = $this->buildMessages($prompt);

        // Prepare vendor options
        $options = array_merge($this->vendorOptions, [
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ]);

        // Call the vendor
        $result = $this->vendor->chat($messages, $options);

        // Store raw result
        $this->lastResult = $result;

        // Add to memory
        $this->addToMemory('user', $prompt);
        $this->addToMemory('assistant', $result);

        // Auto-save memory if key is set
        if ($this->memoryKey) {
            $this->saveMemory();
        }

        // Return based on return type
        return $this->formatResult($result);
    }

    /**
     * Stream response
     */
    public function stream(string $prompt, callable $callback): void
    {
        $messages = $this->buildMessages($prompt);

        $options = array_merge($this->vendorOptions, [
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ]);

        $this->vendor->stream($messages, $callback, $options);
    }

    /**
     * Build messages array for API call
     */
    protected function buildMessages(string $prompt): array
    {
        $messages = [];

        // Add system prompt if set
        if ($this->systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->systemPrompt,
            ];
        }

        // Add memory/history
        foreach ($this->memory as $memoryItem) {
            $messages[] = $memoryItem;
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        return $messages;
    }

    /**
     * Add a message to memory
     */
    protected function addToMemory(string $role, string $content): void
    {
        $this->memory[] = [
            'role' => $role,
            'content' => $content,
        ];
    }

    /**
     * Format result based on return type
     */
    protected function formatResult(string $result): mixed
    {
        return match ($this->returnType) {
            'json' => json_decode($result, true),
            'array' => json_decode($result, true) ?? [],
            'text' => $result,
            'bool', 'boolean' => filter_var($result, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $result,
            'float', 'double' => (float) $result,
            'string' => $result,
            default => $result,
        };
    }

    /**
     * Get the last API response from vendor
     */
    public function getLastResponse(): ?array
    {
        return $this->vendor->getLastResponse();
    }

    /**
     * Get the last raw result
     */
    public function getLastResult(): mixed
    {
        return $this->lastResult;
    }

    /**
     * Get current memory
     */
    public function getMemory(): array
    {
        return $this->memory;
    }

    /**
     * Get memory cache TTL
     */
    public function getMemoryTtl(): int
    {
        return $this->memoryTtl;
    }

    /**
     * Set memory cache TTL
     */
    public function memoryTtl(int $minutes): self
    {
        $this->memoryTtl = $minutes;
        return $this;
    }

    /**
     * Get the current vendor
     */
    public function getVendor(): AiVendorInterface
    {
        return $this->vendor;
    }

    /**
     * Reset all settings to default
     */
    public function reset(): self
    {
        $this->vendor = $this->createDefaultVendor();
        $this->systemPrompt = null;
        $this->memory = [];
        $this->memoryKey = null;
        $this->returnType = 'text';
        $this->temperature = 0.7;
        $this->maxTokens = 1000;
        $this->lastResult = null;
        $this->vendorOptions = [];

        return $this;
    }
}
