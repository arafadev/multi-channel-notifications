<?php

namespace Arafa\Notifications;

use Illuminate\Support\ServiceProvider;
use Arafa\Notifications\NotificationManager;

class MultiChannelNotificationsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');

        $this->app->singleton('notification-manager', function ($app) {
            return new NotificationManager($app['config']['notifications'] ?? []);
        });

        $this->app->alias('notification-manager', NotificationManager::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/notifications.php' => config_path('notifications.php'),
        ], 'notifications-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'notifications-migrations');

        $this->publishes([
            __DIR__ . '/Models/NotificationLog.php' => app_path('Models/NotificationLog.php'),
        ], 'notifications-model');
    }
}
