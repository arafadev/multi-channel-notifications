<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;

class VoiceChannel implements ChannelInterface
{
    protected $client;
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        
        if ($this->isConfigured()) {
            $this->client = new \Twilio\Rest\Client(
                $this->config['account_sid'],
                $this->config['auth_token']
            );
        }
    }
    
    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Voice channel not configured', [], 'voice');
        }
        
        if (!$this->validateRecipient($recipient)) {
            return NotificationResponse::failure('Invalid phone number format', [], 'voice');
        }
        
        try {
            $call = $this->client->calls->create(
                $recipient,
                $this->config['from_number'],
                [
                    'twiml' => $this->generateTwiML($message),
                ]
            );
            
            return NotificationResponse::success(
                $call->sid,
                $call->toArray(),
                'voice'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'voice');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $recipient);
    }
    
    public function getName(): string
    {
        return 'voice';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['account_sid']) && 
               !empty($this->config['auth_token']) && 
               !empty($this->config['from_number']);
    }
    
    protected function generateTwiML(NotificationMessage $message): string
    {
        $voice = $this->config['voice'] ?? 'alice';
        $language = $this->config['language'] ?? 'en-US';
        
        $text = $message->title;
        if ($message->body) {
            $text .= '. ' . $message->body;
        }
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <Response>
                    <Say voice=\"{$voice}\" language=\"{$language}\">{$text}</Say>
                </Response>";
    }
    
}