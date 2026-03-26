<?php

use Illuminate\Support\Facades\Http;
use Lartisan\MailgunClient\Client\ClientConfig;
use Lartisan\MailgunClient\Client\MailgunClient;
use Lartisan\MailgunClient\Facades\Mailgun as MailgunFacade;
use Lartisan\MailgunClient\Mailgun;
use Lartisan\MailgunClient\MailgunServiceProvider;

describe('MailgunServiceProvider', function () {

    it('binds ClientConfig as a singleton', function (): void {
        $config1 = app(ClientConfig::class);
        $config2 = app(ClientConfig::class);

        expect($config1)->toBeInstanceOf(ClientConfig::class)
            ->and($config1)->toBe($config2);
    });

    it('resolves ClientConfig with values from config', function (): void {
        $config = app(ClientConfig::class);

        expect($config->domain)->toBe('test.example.com')
            ->and($config->secret)->toBe('key-test-secret')
            ->and($config->api_key)->toBe('key-test-api')
            ->and($config->sending_api_key)->toBe('key-test-sending')
            ->and($config->endpoint)->toBe('https://api.mailgun.net')
            ->and($config->subscribers_list)->toBe('newsletter@test.example.com');
    });

    it('binds MailgunClient as a singleton', function (): void {
        $client1 = app(MailgunClient::class);
        $client2 = app(MailgunClient::class);

        expect($client1)->toBeInstanceOf(MailgunClient::class)
            ->and($client1)->toBe($client2);
    });

    it('binds Mailgun service as a singleton under the "mailgun" alias', function (): void {
        $mailgun1 = app('mailgun');
        $mailgun2 = app('mailgun');

        expect($mailgun1)->toBeInstanceOf(Mailgun::class)
            ->and($mailgun1)->toBe($mailgun2);
    });

    it('resolves Mailgun via its class name', function (): void {
        expect(app(Mailgun::class))->toBeInstanceOf(Mailgun::class);
    });

    it('registers the package service provider', function (): void {
        expect(app()->getProviders(MailgunServiceProvider::class))->not->toBeEmpty();
    });

});

describe('Mailgun Facade', function () {

    it('resolves to the Mailgun service', function (): void {
        expect(MailgunFacade::getFacadeRoot())->toBeInstanceOf(Mailgun::class);
    });

    it('can be mocked', function (): void {
        MailgunFacade::shouldReceive('sendNewsletter')
            ->once()
            ->with('user@example.com', 'Subject', '<p>Hello</p>');

        MailgunFacade::sendNewsletter('user@example.com', 'Subject', '<p>Hello</p>');
    });

});

describe('Mailgun service', function () {

    it('delegates sendNewsletter to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/*/messages*' => Http::response(['id' => 'x', 'message' => 'Queued. Thank you.'], 200),
        ]);

        app(Mailgun::class)->sendNewsletter('user@example.com', 'My Subject', '<p>Body</p>');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/test.example.com/messages'));
    });

    it('delegates fetchMailingLists to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists*' => Http::response([
                'items' => [
                    [
                        'access_level' => 'readonly',
                        'address' => 'newsletter@test.example.com',
                        'created_at' => '2024-01-01T00:00:00Z',
                        'description' => 'Main newsletter',
                        'members_count' => 5,
                        'name' => 'Newsletter',
                        'reply_preference' => 'list',
                    ],
                ],
            ], 200),
        ]);

        $lists = app(Mailgun::class)->fetchMailingLists();

        expect($lists)->toHaveCount(1);
    });

    it('delegates addMemberToMailingList to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members*' => Http::response([
                'message' => 'Mailing list member has been created',
            ], 200),
        ]);

        $result = app(Mailgun::class)->addMemberToMailingList('new@example.com', 'newsletter@test.example.com');

        expect($result)->toHaveKey('message');
    });

    it('delegates subscribeMemberToMailingList to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members/*' => Http::response([
                'message' => 'Mailing list member has been updated',
            ], 200),
        ]);

        $result = app(Mailgun::class)->subscribeMemberToMailingList('user@example.com', 'newsletter@test.example.com');

        expect($result)->toHaveKey('message');
    });

    it('delegates unsubscribeMemberFromMailingList to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members/*' => Http::response([], 200),
        ]);

        $status = app(Mailgun::class)->unsubscribeMemberFromMailingList('user@example.com', 'newsletter@test.example.com');

        expect($status)->toBe(200);
    });

    it('delegates getAllWebhooks to the underlying client', function (): void {
        Http::fake([
            'api.mailgun.net/v3/domains/*/webhooks*' => Http::response([
                'webhooks' => ['delivered' => ['url' => 'https://example.com/hook']],
            ], 200),
        ]);

        $webhooks = app(Mailgun::class)->getAllWebhooks();

        expect($webhooks)->toHaveKey('delivered');
    });

});
