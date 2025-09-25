<?php

namespace Arafa\Notifications\Channels;

use Log;
use Illuminate\Support\Facades\Mail;
use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;


class EmailChannel implements ChannelInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?? [];
    }

    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->validateRecipient($recipient)) {
            return NotificationResponse::failure('Invalid email address', [], 'email');
        }

        try {
            Mail::raw($message->body, function ($mail) use ($recipient, $message) {
                $mail->to($recipient)
                    ->subject($message->title);

                foreach ($message->attachments as $attachment) {
                    if (file_exists($attachment)) {  
                        if (is_array($attachment)) {
                            $mail->attach($attachment['path'], $attachment['options'] ?? []);
                        } else {
                            $mail->attach($attachment);
                        }
                    } else {
                        Log::warning('Attachment file not found', ['path' => $attachment]);
                    }
                }
            });

            $messageId = 'email_' . time() . '_' . substr(md5($recipient), 0, 8);

            return NotificationResponse::success(
                $messageId,
                ['recipient' => $recipient],
                'email'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'email');
        }
    }

    public function validateRecipient(string $recipient): bool
    {
        return filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getName(): string
    {
        return 'email';
    }

    public function isConfigured(): bool
    {
        return true; // Uses Laravel's mail configuration
    }
}
