<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;

class SlackChannel implements ChannelInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Slack channel not configured', [], 'slack');
        }

        try {
            $token = $this->config['bot_token'];

            $payload = [
                'channel' => $recipient,
                'text'    => $this->formatMessage($message),

            ];

            $response = Http::withToken($token)
                ->post('https://slack.com/api/chat.postMessage', $payload);

            $json = $response->json();

            if ($response->successful() && isset($json['ok']) && $json['ok']) {
                return NotificationResponse::success(
                    $json['ts'] ?? (string) time(),
                    $json,
                    'slack'
                );
            }

            return NotificationResponse::failure(
                $json['error'] ?? 'Unknown Slack API error',
                $json,
                'slack'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'slack');
        }
    }

    protected function formatMessage(NotificationMessage $message): string
    {
        $lines = [];

        if (!empty($message->title)) {
            $lines[] = "*{$message->title}*"; 
        }

        if (!empty($message->body)) {
            $lines[] = $message->body;
        }

        if (!empty($message->data) && is_array($message->data)) {
            foreach ($message->data as $key => $value) {
                $lines[] = "• *{$key}*: {$value}";
            }
        }

        return implode("\n", $lines);
    }



    protected function resolveConversationId(string $recipient, string $token): string
    {
        $recipient = trim($recipient);

        // channel name: #channel
        if (str_starts_with($recipient, '#')) {
            $channelName = ltrim($recipient, '#');
            return $this->findChannelIdByName($channelName, $token);
        }

        // user mention: @username -> open IM and return channel id
        if (str_starts_with($recipient, '@')) {
            $username = ltrim($recipient, '@');
            $userId = $this->findUserIdByUsername($username, $token);
            $open = Http::withToken($token)
                ->post('https://slack.com/api/conversations.open', ['users' => $userId]);
            $oj = $open->json();
            if (isset($oj['ok']) && $oj['ok'] && isset($oj['channel']['id'])) {
                return $oj['channel']['id'];
            }
            throw new \Exception('Unable to open conversation with @' . $username . ' : ' . ($oj['error'] ?? 'unknown'));
        }

        // if it already looks like an ID (C..., G..., D...) return as is
        return $recipient;
    }

    protected function findChannelIdByName(string $name, string $token): string
    {
        $cursor = null;
        do {
            $res = Http::withToken($token)->get('https://slack.com/api/conversations.list', [
                'limit' => 1000,
                'types' => 'public_channel,private_channel',
                'cursor' => $cursor,
            ]);
            $json = $res->json();
            if (!($res->successful() && isset($json['ok']) && $json['ok'])) break;

            foreach ($json['channels'] as $ch) {
                if (($ch['name'] ?? '') === $name || ($ch['name_normalized'] ?? '') === $name) {
                    return $ch['id'];
                }
            }

            $cursor = $json['response_metadata']['next_cursor'] ?? null;
        } while ($cursor);

        throw new \Exception("Channel #{$name} not found or bot not member");
    }

    protected function findUserIdByUsername(string $username, string $token): string
    {
        $cursor = null;
        do {
            $res = Http::withToken($token)->get('https://slack.com/api/users.list', [
                'limit' => 200,
                'cursor' => $cursor,
            ]);
            $json = $res->json();
            if (!($res->successful() && isset($json['ok']) && $json['ok'])) break;

            foreach ($json['members'] as $m) {
                if (($m['name'] ?? '') === $username || ($m['profile']['display_name'] ?? '') === $username) {
                    return $m['id'];
                }
            }

            $cursor = $json['response_metadata']['next_cursor'] ?? null;
        } while ($cursor);

        throw new \Exception("User @{$username} not found");
    }

    public function validateRecipient(string $recipient): bool
    {
        // Channel name (#channel) or user (@user) or empty for default
        return empty($recipient) ||
            str_starts_with($recipient, '#') ||
            str_starts_with($recipient, '@');
    }

    public function getName(): string
    {
        return 'slack';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['bot_token']);
    }

    protected function formatFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = [
                'title' => ucfirst($key),
                'value' => (string) $value,
                'short' => strlen($value) < 20,
            ];
        }
        return $fields;
    }
}
