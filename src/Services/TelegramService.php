<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $chatId;
    protected bool $enabled;

    /**
     * Current update from webhook
     */
    protected ?array $currentUpdate = null;

    public function __construct()
    {
        $this->botToken = config('shopify-enhanced.telegram.bot_token');
        $this->chatId = config('shopify-enhanced.telegram.chat_id');
        $this->enabled = config('shopify-enhanced.telegram.enabled', false);
    }

    /**
     * Set current update from webhook
     */
    public function setCurrentUpdate(array $update): self
    {
        $this->currentUpdate = $update;
        return $this;
    }

    /**
     * Get last/current update
     */
    public function getLastUpdate(): ?array
    {
        return $this->currentUpdate;
    }

    /**
     * Send a text message to configured chat
     */
    public function send(string $message, array $options = []): bool
    {
        return $this->sendToChat($this->chatId, $message, $options);
    }

    /**
     * Send a text message to specific chat
     */
    public function sendToChat(string $chatId, string $message, array $options = []): bool
    {
        if (!$this->isConfigured() && empty($chatId)) {
            return false;
        }

        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post($this->getApiUrl('sendMessage'), $params);
            return $response->successful() && $response->json('ok', false);
        } catch (\Exception $e) {
            Log::error('Telegram send: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a photo
     */
    public function sendPhoto(string $photo, string $caption = '', array $options = []): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $params = array_merge([
                'chat_id' => $this->chatId,
                'photo' => $photo,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post($this->getApiUrl('sendPhoto'), $params);
            return $response->successful() && $response->json('ok', false);
        } catch (\Exception $e) {
            Log::error('Telegram sendPhoto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a document
     */
    public function sendDocument(string $document, string $caption = '', array $options = []): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $params = array_merge([
                'chat_id' => $this->chatId,
                'document' => $document,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::post($this->getApiUrl('sendDocument'), $params);
            return $response->successful() && $response->json('ok', false);
        } catch (\Exception $e) {
            Log::error('Telegram sendDocument: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format message with HTML
     */
    public function formatMessage(array $data): string
    {
        $message = '';

        if (!empty($data['title'])) {
            $message .= "<b>{$data['title']}</b>\n\n";
        }

        if (!empty($data['lines'])) {
            foreach ($data['lines'] as $line) {
                if (is_array($line)) {
                    $key = $line['key'] ?? '';
                    $value = $line['value'] ?? '';
                    $message .= "<b>{$key}:</b> {$value}\n";
                } else {
                    $message .= "{$line}\n";
                }
            }
        }

        if (!empty($data['footer'])) {
            $message .= "\n<i>{$data['footer']}</i>";
        }

        return $message;
    }

    /**
     * Get API URL
     */
    protected function getApiUrl(string $method): string
    {
        return "https://api.telegram.org/bot{$this->botToken}/{$method}";
    }

    /**
     * Check if configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->botToken) && !empty($this->chatId);
    }
}
