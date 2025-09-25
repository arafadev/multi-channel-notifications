<?php

namespace Arafa\Notifications\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;
use Illuminate\Support\Facades\Http;

class DiscordChannel implements ChannelInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        if (!$this->isConfigured()) {
            return NotificationResponse::failure('Discord not configured (missing bot token or guild id)', [], 'discord');
        }

        try {
            $token = $this->config['bot_token'];
            $guildId = $this->config['guild_id'];

            $recipient = trim($recipient);

            // 1) Resolve recipient to a channel ID (either a server channel id or a DM channel id)
            if (preg_match('/^[0-9]+$/', $recipient) || preg_match('/^\@?[0-9]+$/', $recipient)) {
                // numeric -> treat as user ID (open DM)
                $userId = ltrim($recipient, '@');
                $channelId = $this->openDmChannelByUserId($userId, $token);
            } elseif (preg_match('/^[CG][A-Z0-9]+$/i', $recipient)) {
                // already a channel ID (C..., G...)
                $channelId = $recipient;
            } else {
                // channel name (maybe with leading #)
                $channelName = ltrim($recipient, '#');
                $channelId = $this->findChannelIdByName($channelName, $guildId, $token);
            }

            // 2) build payload (embed)
            $embed = $this->buildEmbed($message);

            $payload = [
                'embeds' => [$embed],
                // optionally: 'content' => 'plain text fallback'
            ];

            // 3) send message (Discord wants Authorization: Bot <token>)
            $response = Http::withHeaders([
                'Authorization' => "Bot {$token}",
                'Content-Type' => 'application/json'
            ])
                ->post("https://discord.com/api/v10/channels/{$channelId}/messages", $payload);

            $json = $response->json();

            if ($response->successful()) {
                return NotificationResponse::success(
                    $json['id'] ?? ('discord_' . time()),
                    $json,
                    'discord'
                );
            }

            return NotificationResponse::failure(
                $json['message'] ?? 'Discord API error',
                $json,
                'discord'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure($e->getMessage(), [], 'discord');
        }
    }
    protected function findChannelIdByName(string $name, string $guildId, string $token): string
    {
        $res = Http::withHeaders(['Authorization' => "Bot {$token}"])
            ->get("https://discord.com/api/v10/guilds/{$guildId}/channels");

        $channels = $res->json();

        if (!is_array($channels)) {
            throw new \Exception('Unable to fetch guild channels: ' . json_encode($channels));
        }

        foreach ($channels as $ch) {
            if (($ch['name'] ?? '') === $name) {
                return $ch['id'];
            }
        }

        throw new \Exception("Channel '{$name}' not found in guild {$guildId}");
    }

    protected function openDmChannelByUserId(string $userId, string $token): string
    {
        $res = Http::withHeaders(['Authorization' => "Bot {$token}"])
            ->post('https://discord.com/api/v10/users/@me/channels', ['recipient_id' => $userId]);

        $json = $res->json();

        if (isset($json['id'])) {
            return $json['id'];
        }

        throw new \Exception('Unable to open DM channel: ' . json_encode($json));
    }

    protected function buildEmbed(NotificationMessage $message): array
    {
        // basic embed with fields from $message->data
        $fields = [];
        if (!empty($message->data) && is_array($message->data)) {
            foreach ($message->data as $k => $v) {
                $fields[] = [
                    'name' => ucfirst(str_replace('_', ' ', $k)),
                    'value' => (string)$v,
                    'inline' => true
                ];
            }
        }

        return [
            'title' => $message->title ?? '',
            'description' => $message->body ?? '',
            'color' => hexdec($this->config['color'] ?? '00ff00'),
            'timestamp' => now()->toISOString(),
            'fields' => $fields
        ];
    }


    public function validateRecipient(string $recipient): bool
    {
        // Discord webhooks don't require specific recipient format
        return true;
    }

    public function getName(): string
    {
        return 'discord';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['bot_token']) && !empty($this->config['guild_id']);
    }

    protected function formatFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = [
                'name' => ucfirst($key),
                'value' => (string) $value,
                'inline' => true
            ];
        }
        return $fields;
    }
}
