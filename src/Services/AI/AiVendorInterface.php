<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services\AI;

/**
 * Interface for AI Vendor implementations
 * All AI providers must implement this interface
 */
interface AiVendorInterface
{
    /**
     * Send a chat completion request
     *
     * @param array $messages Array of message arrays with 'role' and 'content'
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return string The AI response content
     * @throws \Exception on API error
     */
    public function chat(array $messages, array $options = []): string;

    /**
     * Stream a chat completion request
     *
     * @param array $messages Array of message arrays
     * @param callable $callback Callback to handle streaming chunks
     * @param array $options Additional options
     * @return void
     */
    public function stream(array $messages, callable $callback, array $options = []): void;

    /**
     * Get the vendor name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the vendor is configured and ready
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the last API response
     *
     * @return array|null
     */
    public function getLastResponse(): ?array;

    /**
     * Get the last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string;
}
