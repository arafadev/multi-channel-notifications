<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;


class TelegramChannel implements ChannelInterface
{
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Telegram channel not configured', [], 'telegram');
        }
        
        if (!$this->validateRecipient($recipient)) {
            return NotificationResponse::failure('Invalid Telegram chat ID or username', [], 'telegram');
        }
        
        try {
            $chatId = $this->resolveChatId($recipient);
            $url = "https://api.telegram.org/bot{$this->config['bot_token']}/sendMessage";
            
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $this->formatMessage($message),
                'parse_mode' => 'HTML',
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return NotificationResponse::success(
                    (string) $data['result']['message_id'],
                    $data,
                    'telegram'
                );
            }
            
            return NotificationResponse::failure(
                $response->json()['description'] ?? 'Unknown Telegram API error',
                $response->json(),
                'telegram'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'telegram');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        // Chat ID (numeric) or username starting with @
        return is_numeric($recipient) || str_starts_with($recipient, '@');
    }
    
    public function getName(): string
    {
        return 'telegram';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['bot_token']);
    }
    
    protected function resolveChatId(string $recipient): string
    {
        return str_starts_with($recipient, '@') ? $recipient : $recipient;
    }
    
  protected function formatMessage(NotificationMessage $message): string
{
    $escape = fn($text) => htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $text = "<b>" . $escape($message->title) . "</b>\n\n" . $escape($message->body);

    if (!empty($message->data)) {
        $text .= "\n\n<i>Additional Info:</i>";
        foreach ($message->data as $key => $value) {
            if (is_array($value) && count($value) === 1) {
                $innerKey = array_key_first($value);
                $innerValue = $value[$innerKey];

                $innerKey = trim($innerKey, "'\"");

                $text .= "\n• <i>" . $escape(ucfirst($innerKey)) . "</i>: " . $escape($innerValue);
                continue;
            }

            // fallback
            $text .= "\n• <i>" . $escape(ucfirst($key)) . "</i>: " . $escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value);
        }
    }

    return $text;
}

}