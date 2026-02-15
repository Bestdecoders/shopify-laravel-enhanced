<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Telegram\Commands;

use Bestdecoders\ShopifyLaravelEnhanced\Services\TelegramService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Help Command
 *
 * Usage: /help - Lists all available commands
 * Usage: /help [command_name] - Shows detailed help for a specific command
 * Usage: /help [command_name] -h - Shows detailed help for a specific command
 *
 * Examples:
 * - /help - Lists all commands
 * - /help discount - Shows help for discount command
 * - /help ai - Shows help for ai command
 */
class HelpCommand
{
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

        if (empty($chatId)) {
            return;
        }

        // Remove /help command prefix to get just the arguments
        $commandText = str_replace('/help', '', $text);
        $commandText = trim($commandText);

        try {
            $parts = explode(' ', $commandText);
            $targetCommand = $parts[0] ?? '';
            $flag = $parts[1] ?? '';

            // If a specific command is requested
            if (!empty($targetCommand)) {
                if ($flag === '-h' || $targetCommand === '-h') {
                    // Show help for all commands if just -h is provided
                    if ($targetCommand === '-h') {
                        $this->listAllCommands($telegram, $chatId);
                    } else {
                        // Show help for specific command
                        $this->showCommandHelp($telegram, $chatId, $targetCommand);
                    }
                } else {
                    // Show help for specific command
                    $this->showCommandHelp($telegram, $chatId, $targetCommand);
                }
            } else {
                // List all available commands
                $this->listAllCommands($telegram, $chatId);
            }

        } catch (\Exception $e) {
            Log::error('Telegram Help Command error', [
                'error' => $e->getMessage(),
                'command' => $commandText,
            ]);

            $telegram->sendToChat($chatId, 'Sorry, I encountered an error processing your command. Please try again.');
        }
    }

    /**
     * List all available commands
     */
    protected function listAllCommands(TelegramService $telegram, string $chatId): void
    {
        $commands = Config::get('shopify-enhanced.telegram.commands', []);

        if (empty($commands)) {
            $telegram->sendToChat($chatId, "No commands are currently available.");
            return;
        }

        $message = "🤖 <b>Available Telegram Commands</b>\n\n";

        foreach ($commands as $commandName => $commandClass) {
            $message .= "<b>/$commandName</b> - ";
            
            // Try to get description from docblock or README
            $description = $this->getCommandDescription($commandName, $commandClass);
            $message .= $description . "\n";
        }

        $message .= "\nUse <b>/help [command_name]</b> to get detailed help for a specific command.";

        $telegram->sendToChat($chatId, $message);
    }

    /**
     * Show detailed help for a specific command
     */
    protected function showCommandHelp(TelegramService $telegram, string $chatId, string $commandName): void
    {
        $commands = Config::get('shopify-enhanced.telegram.commands', []);
        
        if (!isset($commands[$commandName])) {
            $telegram->sendToChat($chatId, "Command '<b>$commandName</b>' not found. Use /help to see all available commands.");
            return;
        }

        $commandClass = $commands[$commandName];
        
        // Try to load the README file for the command
        $readmeContent = $this->getCommandReadme($commandName);
        
        if ($readmeContent) {
            $telegram->sendToChat($chatId, $readmeContent);
        } else {
            // Fallback to docblock comment
            $docblockHelp = $this->getCommandHelpFromDocblock($commandClass);
            if ($docblockHelp) {
                $telegram->sendToChat($chatId, $docblockHelp);
            } else {
                $telegram->sendToChat($chatId, "No detailed help available for command '<b>$commandName</b>'.");
            }
        }
    }

    /**
     * Get command description from docblock or README
     */
    protected function getCommandDescription(string $commandName, string $commandClass): string
    {
        // First try to get from README
        $readmeContent = $this->getCommandReadme($commandName);
        
        if ($readmeContent) {
            // Extract first line or summary from README content
            $lines = explode("\n", $readmeContent);
            foreach ($lines as $line) {
                $line = trim(strip_tags($line)); // Remove HTML tags
                if (!empty($line) && strpos($line, '#') !== 0) { // Skip header lines
                    return $line;
                }
            }
        }

        // Fallback to docblock
        return $this->getShortDescriptionFromDocblock($commandClass) ?: "No description available";
    }

    /**
     * Get command help from docblock comment
     */
    protected function getCommandHelpFromDocblock(string $commandClass): ?string
    {
        if (!class_exists($commandClass)) {
            return null;
        }

        $reflection = new \ReflectionClass($commandClass);
        $docComment = $reflection->getDocComment();

        if ($docComment) {
            // Extract usage examples and description
            $lines = explode("\n", $docComment);
            $helpLines = [];
            $inUsageSection = false;
            
            foreach ($lines as $line) {
                $cleanLine = preg_replace('/^\s*\*\s?/', '', $line); // Remove leading * and spaces
                $cleanLine = trim($cleanLine);
                
                if (strpos($cleanLine, '@') === 0) {
                    continue; // Skip annotations
                }
                
                if (preg_match('/^(Usage|Examples?):/i', $cleanLine)) {
                    $inUsageSection = true;
                    $helpLines[] = "<b>" . ucfirst(strtolower(trim(str_replace(':', '', $cleanLine))) . "</b>");
                    continue;
                }
                
                if ($inUsageSection && !empty($cleanLine) && $cleanLine !== '*') {
                    if (strpos($cleanLine, '-') === 0 || strpos($cleanLine, '/') === 0) {
                        $helpLines[] = "• " . $cleanLine;
                    } else {
                        $helpLines[] = $cleanLine;
                    }
                }
            }
            
            if (!empty($helpLines)) {
                return implode("\n", $helpLines);
            }
        }

        return null;
    }

    /**
     * Get short description from docblock
     */
    protected function getShortDescriptionFromDocblock(string $commandClass): ?string
    {
        if (!class_exists($commandClass)) {
            return null;
        }

        $reflection = new \ReflectionClass($commandClass);
        $docComment = $reflection->getDocComment();

        if ($docComment) {
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                $cleanLine = preg_replace('/^\s*\*\s?/', '', $line);
                $cleanLine = trim($cleanLine);
                
                // Skip empty lines and annotations
                if (empty($cleanLine) || $cleanLine === '*' || strpos($cleanLine, '@') === 0) {
                    continue;
                }
                
                // Return the first meaningful line that's not a usage example
                if (!preg_match('/^(Usage|Examples?):/i', $cleanLine) && 
                    strpos($cleanLine, '/') !== 0 && 
                    strpos($cleanLine, '-') !== 0) {
                    return $cleanLine;
                }
            }
        }

        return null;
    }

    /**
     * Get command README content
     */
    protected function getCommandReadme(string $commandName): ?string
    {
        // Look for README in the command documentation directory
        $readmePath = base_path("package/bestdecoders/shopify-laravel-enhanced/storage/docs/telegram/commands/{$commandName}.md");
        
        if (file_exists($readmePath)) {
            $content = file_get_contents($readmePath);
            // Convert markdown headers to bold for Telegram
            $content = preg_replace('/^# (.+)$/m', '<b>$1</b>', $content);
            $content = preg_replace('/^## (.+)$/m', '<b>$1</b>', $content);
            $content = preg_replace('/^### (.+)$/m', '<b>$1</b>', $content);
            // Convert list items
            $content = preg_replace('/^- /m', '• ', $content);
            $content = preg_replace('/^\d+\. /m', '• ', $content);
            return trim($content);
        }

        return null;
    }
}