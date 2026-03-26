<?php

namespace Lartisan\MailgunClient\Tests;

use Illuminate\Foundation\Application;
use Lartisan\MailgunClient\MailgunServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get the package service providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MailgunServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('mailgun.domain', 'test.example.com');
        $app['config']->set('mailgun.secret', 'key-test-secret');
        $app['config']->set('mailgun.api_key', 'key-test-api');
        $app['config']->set('mailgun.sending_api_key', 'key-test-sending');
        $app['config']->set('mailgun.endpoint', 'https://api.mailgun.net');
        $app['config']->set('mailgun.subscribers_list', 'newsletter@test.example.com');

        $app['config']->set('app.name', 'TestApp');
        $app['config']->set('mail.from.address', 'noreply@test.example.com');
    }
}
