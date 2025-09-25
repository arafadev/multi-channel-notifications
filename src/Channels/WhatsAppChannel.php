<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;


class WhatsAppChannel implements ChannelInterface
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
            return NotificationResponse::failure('WhatsApp channel not configured', [], 'whatsapp');
        }
        
        if (!$this->validateRecipient($recipient)) {
            return NotificationResponse::failure('Invalid WhatsApp number format', [], 'whatsapp');
        }
        
        try {

            $to = str_starts_with($recipient, 'whatsapp:') ? $recipient : 'whatsapp:' . $recipient;
            
            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $this->config['from_number'],
                    'body' => $this->formatMessage($message),
                ]
            );
            
            return NotificationResponse::success(
                $twilioMessage->sid,
                $twilioMessage->toArray(),
                'whatsapp'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'whatsapp');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        $phone = str_replace('whatsapp:', '', $recipient);
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phone);
    }
    
    public function getName(): string
    {
        return 'whatsapp';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['account_sid']) && 
               !empty($this->config['auth_token']) && 
               !empty($this->config['from_number']);
    }
    
    protected function formatMessage(NotificationMessage $message): string
    {
        $text = "*{$message->title}*\n\n{$message->body}";
        
        if (!empty($message->data)) {
            $text .= "\n\n_Additional Info:_";
            foreach ($message->data as $key => $value) {
                $text .= "\n• " . ucfirst($key) . ": " . $value;
            }
        }
        
        return $text;
    }
}



