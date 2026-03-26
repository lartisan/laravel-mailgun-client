<?php

use Lartisan\MailgunClient\Client\ClientConfig;

describe('ClientConfig', function () {

    it('stores all configuration properties', function (): void {
        $config = new ClientConfig(
            domain: 'example.com',
            secret: 'key-secret',
            endpoint: 'https://api.mailgun.net',
            sending_api_key: 'key-sending',
            api_key: 'key-api',
            subscribers_list: 'newsletter@example.com',
        );

        expect($config->domain)->toBe('example.com')
            ->and($config->secret)->toBe('key-secret')
            ->and($config->endpoint)->toBe('https://api.mailgun.net')
            ->and($config->sending_api_key)->toBe('key-sending')
            ->and($config->api_key)->toBe('key-api')
            ->and($config->subscribers_list)->toBe('newsletter@example.com');
    });

    it('is readonly', function (): void {
        $config = new ClientConfig(
            domain: 'example.com',
            secret: 'key-secret',
            endpoint: 'https://api.mailgun.net',
            sending_api_key: 'key-sending',
            api_key: 'key-api',
            subscribers_list: 'newsletter@example.com',
        );

        expect(fn () => $config->domain = 'other.com')
            ->toThrow(Error::class);
    });

});
