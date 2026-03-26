<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lartisan\MailgunClient\Client\ClientConfig;
use Lartisan\MailgunClient\Client\MailgunClient;
use Lartisan\MailgunClient\ValueObjects\MailingList;

/**
 * Build a MailgunClient wired to the test config.
 */
function makeClient(): MailgunClient
{
    return new MailgunClient(new ClientConfig(
        domain: 'test.example.com',
        secret: 'key-test-secret',
        endpoint: 'https://api.mailgun.net',
        sending_api_key: 'key-test-sending',
        api_key: 'key-test-api',
        subscribers_list: 'newsletter@test.example.com',
    ));
}

describe('MailgunClient::fetchMailingLists', function () {

    it('returns a collection of MailingList objects', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists*' => Http::response([
                'items' => [
                    [
                        'access_level' => 'readonly',
                        'address' => 'newsletter@test.example.com',
                        'created_at' => '2024-01-01T00:00:00Z',
                        'description' => 'Main newsletter',
                        'members_count' => 100,
                        'name' => 'Newsletter',
                        'reply_preference' => 'list',
                    ],
                ],
            ], 200),
        ]);

        $result = makeClient()->fetchMailingLists();

        expect($result)->toHaveCount(1)
            ->and($result->first())->toBeInstanceOf(MailingList::class)
            ->and($result->first()->address)->toBe('newsletter@test.example.com');
    });

    it('sends the request with Basic Auth using the secret key', function (): void {
        Http::fake(['api.mailgun.net/v3/lists*' => Http::response(['items' => []], 200)]);

        makeClient()->fetchMailingLists();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/lists')
            && $request->hasHeader('Authorization'));
    });

});

describe('MailgunClient::addMemberToMailingList', function () {

    it('posts to the correct endpoint and returns the response', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members*' => Http::response([
                'member' => ['address' => 'user@example.com', 'subscribed' => false],
                'message' => 'Mailing list member has been created',
            ], 200),
        ]);

        $result = makeClient()->addMemberToMailingList('user@example.com', 'newsletter@test.example.com');

        expect($result)->toHaveKey('message');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/lists/newsletter@test.example.com/members')
            && $request->method() === 'POST');
    });

    it('throws on non-200 response', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        expect(fn () => makeClient()->addMemberToMailingList('user@example.com', 'newsletter@test.example.com'))
            ->toThrow(RequestException::class);
    });

});

describe('MailgunClient::subscribeMemberToMailingList', function () {

    it('puts to the correct endpoint', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members/*' => Http::response([
                'member' => ['address' => 'user@example.com', 'subscribed' => true],
                'message' => 'Mailing list member has been updated',
            ], 200),
        ]);

        $result = makeClient()->subscribeMemberToMailingList('user@example.com', 'newsletter@test.example.com');

        expect($result)->toHaveKey('message');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/lists/newsletter@test.example.com/members/user@example.com')
            && $request->method() === 'PUT');
    });

});

describe('MailgunClient::unsubscribeMemberFromMailingList', function () {

    it('puts subscribed=false and returns the HTTP status code', function (): void {
        Http::fake([
            'api.mailgun.net/v3/lists/*/members/*' => Http::response([], 200),
        ]);

        $status = makeClient()->unsubscribeMemberFromMailingList('user@example.com', 'newsletter@test.example.com');

        expect($status)->toBe(200);
    });

});

describe('MailgunClient::getAllWebhooks', function () {

    it('returns the webhooks array', function (): void {
        Http::fake([
            'api.mailgun.net/v3/domains/*/webhooks*' => Http::response([
                'webhooks' => [
                    'delivered' => ['url' => 'https://example.com/webhooks/delivered'],
                    'complained' => ['url' => 'https://example.com/webhooks/complained'],
                ],
            ], 200),
        ]);

        $result = makeClient()->getAllWebhooks();

        expect($result)->toHaveKeys(['delivered', 'complained'])
            ->and($result['delivered']['url'])->toBe('https://example.com/webhooks/delivered');
    });

    it('hits the correct domain webhooks endpoint', function (): void {
        Http::fake(['api.mailgun.net/v3/domains/*/webhooks*' => Http::response(['webhooks' => []], 200)]);

        makeClient()->getAllWebhooks();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/domains/test.example.com/webhooks'));
    });

});

describe('MailgunClient::send', function () {

    it('posts to the domain messages endpoint', function (): void {
        Http::fake([
            'api.mailgun.net/v3/*/messages*' => Http::response([
                'id' => '<message-id@test.example.com>',
                'message' => 'Queued. Thank you.',
            ], 200),
        ]);

        $result = makeClient()->send([
            'to' => 'recipient@example.com',
            'subject' => 'Test',
            'html' => '<p>Hello</p>',
        ]);

        expect($result)->toHaveKey('message')
            ->and($result['message'])->toBe('Queued. Thank you.');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'v3/test.example.com/messages'));
    });

    it('merges default from/to/reply-to/tracking into the payload', function (): void {
        Http::fake(['api.mailgun.net/v3/*/messages*' => Http::response(['id' => 'x', 'message' => 'Queued. Thank you.'], 200)]);

        makeClient()->send(['subject' => 'Hello', 'html' => '<p>Hi</p>']);

        Http::assertSent(function ($request): bool {
            $body = $request->body();

            return str_contains($body, 'noreply@test.example.com')
                && str_contains($body, 'newsletter@test.example.com')
                && str_contains($body, 'o:tracking');
        });
    });

});
