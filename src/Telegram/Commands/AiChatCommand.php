<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands;

use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;
use Bestdecoders\ShopifyLaravelEnhanced\Services\BrainService;
use Illuminate\Support\Facades\Log;

/**
 * Telegram AI Chat Command
 *
 * Usage: /ai Your question here
 *
 * This command demonstrates using BrainService with Telegram
 * Users can chat with AI through Telegram messages
 */
class AiChatCommand
{
    /**
     * Memory cache key for user conversations
     */
    protected string $memoryKeyPrefix = 'telegram_ai_chat_';

    /**
     * Handle command
     *
     * @param TelegramService $telegram The Telegram service (injected by WebhookHandler)
     * @return void
     */
    public function handle(TelegramService $telegram): void
    {
        // Get update from Telegram service context
        $update = $telegram->getLastUpdate();

        if (empty($update)) {
            return;
        }

        $message = $update['message'] ?? [];
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;

        if (empty($chatId)) {
            return;
        }

        // Check if brain is configured (NOW we have chatId)
        if (!$this->isBrainConfigured()) {
            $telegram->sendToChat($chatId, 'Please configure brain first. Add OPENAI_API_KEY or ANTHROPIC_API_KEY to your .env file.');
            return;
        }

        // Remove /ai command prefix
        $question = str_replace('/ai', '', $text);
        $question = trim($question);

        if (empty($question)) {
            $telegram->sendToChat($chatId, 'Please ask me something! Usage: /ai Your question');
            return;
        }

        try {
            // Get AI response
            $response = $this->askAi($question, $userId);

            // Send response
            $telegram->sendToChat($chatId, $response);

        } catch (\Exception $e) {
            Log::error('Telegram AI Chat error', [
                'error' => $e->getMessage(),
                'user' => $userId,
                'question' => $question,
            ]);

            $telegram->sendToChat($chatId, 'Sorry, I encountered an error. Please try again.');
        }
    }

    /**
     * Ask AI a question
     *
     * @param string $question The user's question
     * @param string|null $userId The user's ID
     * @return string The AI response
     */
    protected function askAi(string $question, ?string $userId): string
    {
        $brain = app(BrainService::class);

        $memoryKey = $this->memoryKeyPrefix . ($userId ?? 'anonymous');

        return $brain
            ->systemPrompt('You are a helpful AI assistant. Keep responses concise and friendly.')
            ->loadMemory($memoryKey)
            ->maxTokens(500)
            ->ask($question);
    }

    /**
     * Check if brain/AI is configured
     *
     * @return bool
     */
    protected function isBrainConfigured(): bool
    {
        $provider = config('shopify-enhanced.brain.default_provider', 'openai');

        return match ($provider) {
            'openai' => !empty(config('shopify-enhanced.brain.providers.openai.api_key')),
            'anthropic' => !empty(config('shopify-enhanced.brain.providers.anthropic.api_key')),
            default => false,
        };
    }
}
