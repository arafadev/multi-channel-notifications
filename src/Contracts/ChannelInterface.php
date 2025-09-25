<?php

namespace Arafa\Notifications\Contracts;

use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;

interface ChannelInterface
{
    /**
     * Send notification via this channel
     */
    public function send(string $recipient, NotificationMessage $message): NotificationResponse;
    
    /**
     * Validate if recipient is valid for this channel
     */
    public function validateRecipient(string $recipient): bool;
    
    /**
     * Get channel name
     */
    public function getName(): string;
    
    /**
     * Check if channel is configured properly
     */
    public function isConfigured(): bool;
}