<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | The default channel to use when none is specified
    |
    */
    'default_channel' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Channel Configurations
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'email' => [
            'driver' => 'mail', // Laravel's built-in mail
        ],

        'sms' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
        ],

        'whatsapp' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+201062204741'),
        ],

        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        ],

        'slack' => [
            'bot_token' => env('SLACK_BOT_TOKEN'),
        ],

        'discord' => [
            'bot_token' => env('DISCORD_BOT_TOKEN'),
            'guild_id' => env('DISCORD_GUILD_ID'),
        ],

        'teams' => [
            'webhook_url' => env('TEAMS_WEBHOOK_URL'),
        ],

        'messenger' => [
            'page_access_token' => env('MESSENGER_PAGE_ACCESS_TOKEN'),
            'facebook_user_id' => env('MESSENGER_FACEBOOK_USER_ID'),
        ],

        'voice' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_VOICE_FROM_NUMBER'),
            
        ],

    ],

   
];
