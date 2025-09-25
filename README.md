# Multi-Channel Notifications for Laravel

🚀 A comprehensive Laravel package that enables you to send notifications across multiple channels from a single, unified interface. Send emails, SMS, WhatsApp messages, Slack notifications, Discord messages, and more with consistent API.

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

## 🔊 Voice Example

**Required:** `recipient` (E.164 format), `title`, `body`  
**Optional:** `data`, `options`  
**Max body length:** 1000 characters

```php
// Basic voice call
$message = NotificationMessage::create(
    'Emergency Alert',  // required: title
    'This is an urgent security notification.'  // required: body (max 1000 chars)
);

$response = Notify::send('+1234567890', $message, 'voice');  // required: E.164 format

// Voice with options (optional)
$message = NotificationMessage::create(
    'System Alert',
    'Your server is down.'
)->withOptions([
    'voice' => 'alice',
    'language' => 'en-US'
]);

$response = Notify::send('+1234567890', $message, 'voice');
```

## 📱 WhatsApp Example

**Required:** `recipient` (whatsapp:+phone format), `title`, `body`  
**Optional:** `data`, `options`

```php
// Basic WhatsApp
$message = NotificationMessage::create(
    'Order Update',  // required: title
    'Your order #12345 has been shipped!'  // required: body
);

$response = Notify::send('whatsapp:+1234567890', $message, 'whatsapp');  // required: whatsapp: prefix

// WhatsApp with tracking data (optional)
$message = NotificationMessage::create(
    'Delivery Status',
    'Your package is on its way!'
)->withData([
    'tracking' => 'TRK123456789',
    'carrier' => 'UPS',
    'eta' => '2 hours'
]);

$response = Notify::send('whatsapp:+1234567890', $message, 'whatsapp');
```

## 🧑‍💻 Slack Example

**Required:** `recipient`, `title`, `body`  
**Optional:** `data`, `options`

```php
// Basic Slack message
$message = NotificationMessage::create(
    'Deployment Complete',  // required: title
    'App deployed to production successfully!'  // required: body
);

$response = Notify::send('#general', $message, 'slack');  // required: channel or user

// Send to multiple recipients
$recipients = explode(',', env('SLACK_CHANNELS')); // all-testworkspace,social,engineering_tasks,@arafa.dev

foreach ($recipients as $recipient) {
    $responses[] = Notify::send(trim($recipient), $message, 'slack');
}

// Slack with deployment data (optional)
$message = NotificationMessage::create(
    'Release v2.1.0',
    'New version deployed successfully.'
)->withData([
    'environment' => 'production',
    'commit' => 'abc123',
    'deploy_time' => '2 minutes'
]);

$response = Notify::send('@arafa.dev', $message, 'slack');
```

## 📧 Email Example

**Required:** `recipient`, `title`, `body`  
**Optional:** `data`, `attachments`, `options`

```php
use Arafa\Notifications\Facades\Notify;
use Arafa\Notifications\Messages\NotificationMessage;

// Basic email
$message = NotificationMessage::create(
    'Account Verification',  // required: title
    'Please verify your email address.'  // required: body
);

$response = Notify::send('user@example.com', $message, 'email');

// Email with attachments (optional)
$message = NotificationMessage::create(
    'Welcome Package',
    'Welcome to our platform! Please find attached documents.'
)->withAttachments([
    '/path/to/welcome-guide.pdf',
    '/path/to/terms.pdf'
]);

$response = Notify::send('user@example.com', $message, 'email');

// Email with additional data (optional)
$message = NotificationMessage::create(
    'Account Created',
    'Your account has been successfully created.'
)->withData([
    'user_id' => 123,
    'plan' => 'Premium'
]);

$response = Notify::send('user@example.com', $message, 'email');
```

## 🟣 Discord Example

**Required:** `recipients`, `title`, `body`  
**Optional:** `data`, `options`

```php
// Basic Discord message
$message = NotificationMessage::create(
    'Server Status',  // required: title
    'All systems operational.'  // required: body
)->withData([  // optional: additional info
    'uptime' => '99.9%',
    'response_time' => '150ms'
]);

$response = Notify::send('general', $message, 'discord');  // required: channel name

// Send to multiple channels
$recipients = explode(',', env('DISCORD_RECIPIENTS')); // #general,#alerts,#dev-team

foreach ($recipients as $recipient) {
    $responses[] = Notify::send(trim($recipient), $message, 'discord');
}

// Send DM to user (use user ID)
$response = Notify::send('123456789', $message, 'discord');
```

## 👥 Teams Example

**Required:** `title`, `body`  
**Optional:** `data`, `options`  
**Note:** Recipient not required (uses webhook URL)

```php
// Basic Teams message
$message = NotificationMessage::create(
    'Meeting Reminder',  // required: title
    'Team standup in 15 minutes.'  // required: body
);

$response = Notify::send('webhook', $message, 'teams');  // recipient can be any string

// Teams with meeting details (optional)
$message = NotificationMessage::create(
    'Project Update',
    'Weekly project status update.'
)->withData([  // optional: shows as facts in Teams
    'progress' => '75%',
    'deadline' => '2024-01-15',
    'team_size' => '8 people'
]);

$response = Notify::send('', $message, 'teams');
```

## 💬 SMS Example

**Required:** `recipient` (E.164 format), `title`, `body`  
**Optional:** `data`, `options`  
**Max body length:** 1600 characters

```php
// Basic SMS
$message = NotificationMessage::create(
    'Security Alert',  // required: title
    'Your account was accessed from a new device.'  // required: body (max 1600 chars)
);

$response = Notify::send('+1234567890', $message, 'sms');  // required: E.164 format

// SMS with additional data (optional)
$message = NotificationMessage::create(
    'Login Alert',
    'New login detected.'
)->withData([
    'ip' => '192.168.1.1',
    'device' => 'iPhone'
]);

$response = Notify::send('+1234567890', $message, 'sms');
```

## 📢 Telegram Example

**Required:** `recipient`, `title`, `body`  
**Optional:** `data`, `options`

```php
// Basic Telegram message
$message = NotificationMessage::create(
    'Price Alert',  // required: title
    'Car reached $50,000!'  // required: body
);

$response = Notify::send('@username', $message, 'telegram');  // required: @username or chat_id

// Telegram with price data (optional)
$message = NotificationMessage::create(
    'Market Update',
    'Cryptocurrency prices updated.'
)->withData([  // optional: shows as formatted info
    'btc_price' => '$50,125',
    'change' => '+2.5%',
    'volume' => '$2.1B'
]);

// Send to chat ID (numeric)
$response = Notify::send('123456789', $message, 'telegram');
```

## 📨 Messenger Example

**Required:** `recipient` (Facebook user ID), `title`, `body`  
**Optional:** `data`, `options`

```php
// Basic Messenger message
$message = NotificationMessage::create(
    'New Message',  // required: title
    'You have a message from support.'  // required: body
);

$response = Notify::send('1234567890', $message, 'messenger');  // required: numeric Facebook user ID

// Messenger with support ticket data (optional)
$message = NotificationMessage::create(
    'Support Ticket #456',
    'Your ticket has been updated.'
)->withData([  // optional: additional context
    'status' => 'In Progress',
    'agent' => 'John Doe',
    'priority' => 'High'
]);

$response = Notify::send(env('MESSENGER_FACEBOOK_USER_ID'), $message, 'messenger');
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
