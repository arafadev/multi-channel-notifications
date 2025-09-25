<?php

namespace Arafa\Notifications;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Arafa\Notifications\Models\NotificationLog;
use Arafa\Notifications\Exceptions\ChannelNotFound;
use Arafa\Notifications\Exceptions\NotificationFailed;
use Illuminate\Support\Facades\Log;

class NotificationManager
{
    protected array $channels = [];
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->registerDefaultChannels();

        // dd($this->channels, $this->config);
    }

    /**
     * Send notification immediately
     */
    public function send(string $recipient, NotificationMessage $message, ?string $channel = null): NotificationResponse
    {
        try {
            $channelInstance = $this->resolveChannel($recipient, $channel);
            $response = $channelInstance->send($recipient, $message);

            $this->logNotification($recipient, $message, $response, $channelInstance->getName());

            return $response;
        } catch (\Exception $e) {
            return $this->handleFailure($recipient, $message, $e, $channel);
        }
    }

    /**
     * Register a custom channel
     */
    public function registerChannel(string $name, ChannelInterface $channel): self
    {
        $this->channels[$name] = $channel;
        return $this;
    }

    /**
     * Get available channels
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->channels);
    }

    protected function registerDefaultChannels(): void
    {
        $channelClasses = [
            'email'     => \Arafa\Notifications\Channels\EmailChannel::class,
            'sms'       => \Arafa\Notifications\Channels\SmsChannel::class,
            'whatsapp'  => \Arafa\Notifications\Channels\WhatsAppChannel::class,
            'telegram'  => \Arafa\Notifications\Channels\TelegramChannel::class,
            'slack'     => \Arafa\Notifications\Channels\SlackChannel::class,
            'discord'   => \Arafa\Notifications\Channels\DiscordChannel::class,
            'teams'     => \Arafa\Notifications\Channels\TeamsChannel::class,
            'messenger' => \Arafa\Notifications\Channels\MessengerChannel::class,
            'voice'     => \Arafa\Notifications\Channels\VoiceChannel::class,
        ];

         foreach ($channelClasses as $name => $class) {
        if (class_exists($class)) {
            $channelConfig = isset($this->config['channels'][$name]) ? $this->config['channels'][$name] : [];
            $this->channels[$name] = new $class($channelConfig);
        }
    }
    }

    protected function resolveChannel(string $recipient, ?string $channel = null): ChannelInterface
    {
        if ($channel) {
            return $this->getChannel($channel);
        }

        return $this->getChannel($this->detectChannelForRecipient($recipient));
    }

    protected function getChannel(string $name): ChannelInterface
    {
        if (!isset($this->channels[$name])) {
            throw new ChannelNotFound("Channel '{$name}' not found");
        }

        return $this->channels[$name];
    }

    protected function detectChannelForRecipient(string $recipient): string
    {
        // Email detection
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Phone number detection (simple pattern)
        if (preg_match('/^\+?[1-9]\d{1,14}$/', $recipient)) {
            return 'sms';
        }

        // Telegram username
        if (str_starts_with($recipient, '@')) {
            return 'telegram';
        }

        return $this->config['default_channel'] ?? 'email';
    }

    protected function handleFailure(string $recipient, NotificationMessage $message, \Exception $e, ?string $channel = null): NotificationResponse
    {
        $response = NotificationResponse::failure($e->getMessage(), [], $channel);
        $this->logNotification($recipient, $message, $response, $channel);

        if ($this->config['reliability']['throw_on_failure'] ?? false) {
            throw new NotificationFailed($e->getMessage(), 0, $e);
        }

        return $response;
    }

    protected function logNotification(string $recipient, NotificationMessage $message, NotificationResponse $response, ?string $channel = null): void
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $logData = [
            'recipient' => $recipient,
            'channel' => $channel,
            'title' => $message->title,
            'body_preview' => substr($message->body, 0, 200) . (strlen($message->body) > 200 ? '...' : ''),
            'full_body' => $this->config['logging']['detailed_email_logs'] ?? false ? $message->body : null,
            'data' => $message->data,
            'attachments' => array_map(function ($att) {
                return is_array($att) ? $att['path'] : $att;
            }, $message->attachments),
            'status' => $response->success ? 'sent' : 'failed',
            'message_id' => $response->messageId,
            'error' => $response->error,
            'provider_response' => $response->providerResponse,
            'timestamp' => now()->toISOString(),
        ];

        // Database loggingx
        if ($this->config['logging']['database'] ?? true) {
            try {
                NotificationLog::create([
                    'recipient' => $recipient,
                    'channel' => $channel,
                    'title' => $message->title,
                    'body' => $message->body,
                    'data' => $message->data,
                    'status' => $response->success ? 'sent' : 'failed',
                    'message_id' => $response->messageId,
                    'error' => $response->error,
                    'provider_response' => $response->providerResponse,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to log to database', ['error' => $e->getMessage()]);
            }
        }

        // File logging with detailed info
        if ($this->config['logging']['file'] ?? true) {
            \Log::channel('notifications')->info('📧 Email Notification Processed', $logData);
        }
    }
}
