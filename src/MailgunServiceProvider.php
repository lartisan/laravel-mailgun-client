<?php

namespace Lartisan\MailgunClient;

use Illuminate\Support\ServiceProvider;
use Lartisan\MailgunClient\Client\ClientConfig;
use Lartisan\MailgunClient\Client\MailgunClient;
use Lartisan\MailgunClient\Logging\RequestLogger;
use Psr\Log\LoggerInterface;

class MailgunServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mailgun.php', 'mailgun');

        $this->app->singleton(ClientConfig::class, function ($app): ClientConfig {
            return new ClientConfig(
                domain: $app['config']['mailgun.domain'],
                secret: $app['config']['mailgun.secret'],
                endpoint: $app['config']['mailgun.endpoint'],
                sending_api_key: $app['config']['mailgun.sending_api_key'],
                api_key: $app['config']['mailgun.api_key'],
                subscribers_list: $app['config']['mailgun.subscribers_list'],
            );
        });

        $this->app->singleton(RequestLogger::class, function ($app): RequestLogger {
            return new RequestLogger($app->make(LoggerInterface::class));
        });

        $this->app->singleton(MailgunClient::class, function ($app): MailgunClient {
            return new MailgunClient($app->make(ClientConfig::class));
        });

        $this->app->singleton('mailgun', function ($app): Mailgun {
            return new Mailgun($app->make(MailgunClient::class));
        });

        $this->app->alias('mailgun', Mailgun::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mailgun.php' => config_path('mailgun.php'),
            ], 'mailgun-config');
        }
    }
}
