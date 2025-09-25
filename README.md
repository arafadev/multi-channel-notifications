# Multi-Channel Notifications for Laravel

🚀 A comprehensive Laravel package that enables you to send notifications across multiple channels from a single, unified interface. Send emails, SMS, WhatsApp messages, Slack notifications, Discord messages, and more with consistent API.

## 📦 Installation

Install the package via Composer:

```bash
composer require arafadev/multi-channel-notifications
```

### ✅ Publish Configuration

```bash
php artisan vendor:publish --provider="Arafa\Notifications\MultiChannelNotificationsServiceProvider" --tag="notifications-config"
```

### 🛠️ Publish Model

```bash
php artisan vendor:publish --provider="Arafa\Notifications\MultiChannelNotificationsServiceProvider" --tag="notifications-model"
```

### 🛠️ Run Migrations

```bash
php artisan vendor:publish --provider="Arafa\Notifications\MultiChannelNotificationsServiceProvider" --tag="notifications-migrations"
php artisan migrate
```

## ⚙️ Configuration

Add your credentials to `.env` file:

```env
# Email (uses Laravel mail config)

# SMS & WhatsApp & Voice (Twilio)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
TWILIO_WHATSAPP_FROM=whatsapp:+1234567890

# Telegram
TELEGRAM_BOT_TOKEN=your_bot_token

# Slack
SLACK_BOT_TOKEN=xoxb-your-slack-token

# Discord
DISCORD_BOT_TOKEN=your_discord_token
DISCORD_GUILD_ID=your_guild_id

# Teams
TEAMS_WEBHOOK_URL=your_webhook_url

# Messenger
MESSENGER_PAGE_ACCESS_TOKEN=your_page_token
```


## 📡 Supported Channels

| Channel   | Emoji | Provider     | Recipient Format            |
| --------- | ----- | ------------ | --------------------------- |
| Email     | 📧    | Laravel Mail | `user@example.com`          |
| SMS       | 💬    | Twilio       | `+1234567890`               |
| WhatsApp  | 📱    | Twilio       | `+1234567890`               |
| Voice     | 🔊    | Twilio       | `+1234567890`               |
| Slack     | 🧑‍💻    | Slack API    | `#channel` or `@user`       |
| Discord   | 🟣    | Discord API  | `channel_name` or `user_id` |
| Teams     | 👥    | Webhooks     | any string                  |
| Telegram  | 📢    | Bot API      | `@username` or `chat_id`    |
| Messenger | 📨    | Facebook API | `facebook_user_id`          |


## 🔊 Voice Example

```php
$message = NotificationMessage::create(
    'Emergency Alert',
    'This is an urgent security notification.'
);

$response = Notify::send('+1234567890', $message, 'voice');
```

## 📱 WhatsApp Example

```php
$message = NotificationMessage::create(
    'Order Update',
    'Your order #12345 has been shipped!'
)->withData([
    'tracking' => 'TRK123456789'
]);

$response = Notify::send('+1234567890', $message, 'whatsapp');
```

## 🧑‍💻 Slack Example

```php
$message = NotificationMessage::create(
    'Deployment Complete',
    'App deployed to production successfully!'
);

    // Send to multiple channels and users (comma-separated)
$recipients = explode(',', env('SLACK_CHANNELS')); // all-testworkspace,social,engineering_tasks,@arafa.dev

foreach ($recipients as $recipient) {
    $responses[] = Notify::send(trim($recipient), $message, 'slack');
}

return response()->json([
    'success' => collect($responses)->every(fn($r) => $r->isSuccess()),
    'responses' => array_map(fn($r) => [
        'message_id' => $r->messageId,
        'provider_response' => $r->providerResponse,
        'error' => $r->error,
    ], $responses),
]);
```
## 📧 Email Example

```php
$message = NotificationMessage::create(
    'Account Verification',
    'Please verify your email address.'
);

$response = Notify::send('user@example.com', $message, 'email');
```

## 🟣 Discord Example

```php
$message = NotificationMessage::create(
    'Server Status',
    'All systems operational.'
    )->withData([
        'uptime' => '99.9%'
    ]);

// Send to multiple channels (comma-separated)
$recipients = explode(',', env('DISCORD_RECIPIENTS')); // #general,

foreach ($recipients as $recipient) {
    $responses[] = Notify::send(trim($recipient), $message, 'discord');
}

return response()->json([
    'success' => collect($responses)->every(fn($r) => $r->isSuccess()),
    'responses' => array_map(fn($r) => [
        'message_id' => $r->messageId,
        'provider_response' => $r->providerResponse,
        'error' => $r->error,
    ], $responses),
]);
```

## 👥 Teams Example

```php
$message = NotificationMessage::create(
    'Meeting Reminder',
    'Team standup in 15 minutes.'
);

$response = Notify::send('webhook', $message, 'teams');

return response()->json($response);
```

## 💬 SMS Example

```php
$message = NotificationMessage::create(
    'Security Alert',
    'Your account was accessed from a new device.'
);

$response = Notify::send('+1234567890', $message, 'sms');
```

## 📢 Telegram Example

```php
$message = NotificationMessage::create(
    'Price Alert',
    'Bitcoin reached $50,000!'
)->withData([
    'price' => '$50,125'
]);

// Send to chat
$response = Notify::send('123456789', $message, 'telegram');
```

## 📨 Messenger Example

```php
$message = NotificationMessage::create(
    'New Message',
    'You have a message from support.'
);

$response = Notify::send(env('MESSENGER_FACEBOOK_USER_ID'), $message, 'messenger');

return response()->json($response);
```

## 🔧 Custom Channel

Create your own notification channel:

```php
<?php

namespace App\Channels;

use Arafa\Notifications\Contracts\ChannelInterface;
use Arafa\Notifications\Messages\NotificationMessage;
use Arafa\Notifications\Responses\NotificationResponse;

class MyCustomChannel implements ChannelInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $recipient, NotificationMessage $message): NotificationResponse
    {
        // Your sending logic here
        try {
            $result = $this->sendMessage($recipient, $message);

            return NotificationResponse::success(
                $result['id'],
                $result,
                'custom'
            );
        } catch (\Exception $e) {
            return NotificationResponse::failure(
                $e->getMessage(),
                [],
                'custom'
            );
        }
    }

    public function validateRecipient(string $recipient): bool
    {
        return !empty($recipient);
    }

    public function getName(): string
    {
        return 'custom';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }

    private function sendMessage($recipient, $message)
    {
        // Your custom provider logic
        return ['id' => 'custom_' . time()];
    }
}
```

### Register & Use Custom Channel

```php
use Arafa\Notifications\Facades\Notify;
use App\Channels\MyCustomChannel;

// Register
$channel = new MyCustomChannel(['api_key' => 'your-key']);
Notify::registerChannel('custom', $channel);

// Use
$response = Notify::send('recipient', $message, 'custom');
```

## 📄 License

MIT License

---

**Made by [Arafa](https://github.com/arafadev) ❤️**
