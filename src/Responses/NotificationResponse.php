<?php

namespace Arafa\Notifications\Responses;

class NotificationResponse
{
    public function __construct(
        public bool $success,
        public ?string $messageId = null,
        public ?string $error = null,
        public array $providerResponse = [],
        public ?string $channel = null
    ) {}
    
    public static function success(string $messageId, array $providerResponse = [], ?string $channel = null): self
    {
        return new self(true, $messageId, null, $providerResponse, $channel);
    }
    
    public static function failure(string $error, array $providerResponse = [], ?string $channel = null): self
    {
        return new self(false, null, $error, $providerResponse, $channel);
    }
    
    public function isSuccess(): bool
    {
        return $this->success;
    }
    
    public function isFailed(): bool
    {
        return !$this->success;
    }
    
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message_id' => $this->messageId,
            'error' => $this->error,
            'provider_response' => $this->providerResponse,
            'channel' => $this->channel,
        ];
    }
}