<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;

class TeamsChannel implements ChannelInterface
{
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Teams channel not configured', [], 'teams');
        }
        
        try {
            $payload = [
                '@type' => 'MessageCard',
                '@context' => 'http://schema.org/extensions',
                'themeColor' => $this->config['theme_color'] ?? '0076D7',
                'summary' => $message->title,
                'sections' => [
                    [
                        'activityTitle' => $message->title,
                        'activitySubtitle' => now()->format('Y-m-d H:i:s'),
                        'text' => $message->body,
                        'facts' => $this->formatFacts($message->data),
                    ]
                ]
            ];
            
            $response = Http::post($this->config['webhook_url'], $payload);
            
            if ($response->successful()) {
                return NotificationResponse::success(
                    'teams_' . time(),
                    ['status_code' => $response->status()],
                    'teams'
                );
            }
            
            return NotificationResponse::failure(
                'Teams webhook failed: ' . $response->status(),
                $response->json(),
                'teams'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'teams');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        // Teams webhooks don't require specific recipient format
        return true;
    }
    
    public function getName(): string
    {
        return 'teams';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['webhook_url']);
    }
    
    protected function formatFacts(array $data): array
    {
        $facts = [];
        foreach ($data as $key => $value) {
            $facts[] = [
                'name' => ucfirst($key),
                'value' => (string) $value,
            ];
        }
        return $facts;
    }
}

