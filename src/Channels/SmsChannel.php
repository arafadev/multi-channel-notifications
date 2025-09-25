<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Twilio\Rest\Client;

class SmsChannel implements ChannelInterface
{
    protected Client $client;
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        
        if ($this->isConfigured()) {
            $this->client = new Client(
                $this->config['account_sid'],
                $this->config['auth_token']
            );
        }
    }
    
    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('SMS channel not configured', [], 'sms');
        }
        
        if (!$this->validateRecipient($recipient)) {
            return NotificationResponse::failure('Invalid phone number format', [], 'sms');
        }
        
        try {
            $twilioMessage = $this->client->messages->create(
                $recipient,
                [
                    'from' => $this->config['from_number'],
                    'body' => $this->formatMessage($message),
                ]
            );

            
            return NotificationResponse::success(
                $twilioMessage->sid,
                $twilioMessage->toArray(),
                'sms'
            );
        } catch (\Exception $e) {
            // dd($e); // code arrive here
            return NotificationResponse::failure($e->getMessage(), [], 'sms');
        }
    }
    
    public function validateRecipient(string $recipient): bool
    {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $recipient);
    }
    
    public function getName(): string
    {
        return 'sms';
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->config['account_sid']) && 
               !empty($this->config['auth_token']) && 
               !empty($this->config['from_number']);
    }
    
    protected function formatMessage(NotificationMessage $message): string
    {
        $text = $message->title;
        if ($message->body) {
            $text .= "\n\n" . $message->body;
        }
        
        return $text;
    }
}