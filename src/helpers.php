<?php

if (!function_exists('debug_log')) {
    function debug_log($message, array $context = []) {
        if (config('app.debug')) {
            info($message, $context);
        }
    }
}

if (!function_exists('useBrain')) {
    /**
     * Get a BrainService instance for AI interactions
     *
     * Usage examples:
     * // Simple usage with prompt
     * $result = useBrain('Your prompt here');
     *
     * // With system prompt
     * $result = useBrain()->systemPrompt('You are a helpful assistant')->ask('Your question');
     *
     * // With memory from cache
     * $result = useBrain()->loadMemory('conversation_key')->ask('Follow-up question');
     *
     * // With specific return type
     * $data = useBrain()->returnType('json')->ask('Return JSON data');
     *
     * // With custom provider
     * $result = useBrain()->provider('anthropic')->ask('Your question');
     *
     * @param string|null $prompt Optional prompt to ask immediately
     * @return \Bestdecoders\ShopifyLaravelEnhanced\Services\BrainService|string
     */
    function useBrain(?string $prompt = null) {
        $brain = app(\Bestdecoders\ShopifyLaravelEnhanced\Services\BrainService::class);

        // If prompt provided, execute and return result
        if ($prompt !== null) {
            return $brain->ask($prompt);
        }

        return $brain;
    }
}