<?php

namespace Arafa\Notifications\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Arafa\Notifications\Responses\NotificationResponse send(string $recipient, \Arafa\Notifications\Messages\NotificationMessage $message, ?string $channel = null)
 * @method static void queue(string $recipient, \Arafa\Notifications\Messages\NotificationMessage $message, ?string $channel = null, ?string $queue = null)
 * @method static \Arafa\Notifications\Responses\NotificationResponse sendReliable(string $recipient, \Arafa\Notifications\Messages\NotificationMessage $message, ?string $channel = null)
 * @method static array broadcast(array $recipients, \Arafa\Notifications\Messages\NotificationMessage $message, ?string $channel = null)
 * @method static \Arafa\Notifications\NotificationManager registerChannel(string $name, \Arafa\Notifications\Contracts\ChannelInterface $channel)
 */
class Notify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'notification-manager';
    }
}