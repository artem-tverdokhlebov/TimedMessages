<?php

namespace Ridex\TimedMessages;

use Illuminate\Support\ServiceProvider;

class TimedMessagesServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'timed-messages-config');
            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'timed-messages-migrations');
        }
    }
}