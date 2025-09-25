<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;


class MessengerChannel implements ChannelInterface
{
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Messenger channel not configured', [], 'messenger');
        }
        
        try {
            $payload = [
                'recipient' => ['id' => $recipient],
                'message' => [
                    'text' => $this->formatMessage($message)
                ]
            ];
            
            $response = Http::withToken($this->config['page_access_token'])
                ->post('https://graph.facebook.com/v18.0/me/messages', $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                return NotificationResponse::success(
                    $data['message_id'] ?? 'unknown',
                    $data,
                    'messenger'
                );
            }
            
            return NotificationResponse::failure(
                'Messenger API error: ' . ($response->json()['error']['message'] ?? 'Unknown error'),
                $response->json(),
                'messenger'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'messenger');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        // Facebook user ID (numeric string)
        return is_numeric($recipient);
    }
    
    public function getName(): string
    {
        return 'messenger';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['page_access_token']);
    }
    
    protected function formatMessage(NotificationMessage $message): string
    {
        $text = "{$message->title}\n\n{$message->body}";
        
        if (!empty($message->data)) {
            $text .= "\n\nAdditional Info:";
            foreach ($message->data as $key => $value) {
                $text .= "\n• " . ucfirst($key) . ": " . $value;
            }
        }
        
        return $text;
    }
}