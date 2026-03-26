<?php

namespace Lartisan\MailgunClient\Client;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lartisan\MailgunClient\Logging\RequestLogger;
use Lartisan\MailgunClient\ValueObjects\MailingList;
use Symfony\Component\HttpFoundation\Response;

class MailgunClient
{
    public function __construct(
        protected ClientConfig $config,
    ) {}

    /**
     * @return Collection<int, MailingList>|null
     */
    public function fetchMailingLists(): ?object
    {
        $response = $this->get($this->config->secret, 'v3/lists');

        return rescue(
            fn () => MailingList::toCollection($response->json('items'))
        );
    }

    /** @throws RequestException|ConnectionException */
    public function addMemberToMailingList(string $email, string $mailingList): mixed
    {
        $response = $this->post($this->config->api_key, "v3/lists/{$mailingList}/members", [
            'address' => $email,
            'subscribed' => 0,
            'upsert' => true,
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            return $response->throw();
        }

        return $response->json();
    }

    /** @throws RequestException */
    public function subscribeMemberToMailingList(string $email, string $mailingList): mixed
    {
        $response = $this->put($this->config->api_key, "v3/lists/{$mailingList}/members/{$email}", [
            'address' => $email,
            'subscribed' => 1,
            'upsert' => true,
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            return $response->throw();
        }

        return $response->json();
    }

    public function unsubscribeMemberFromMailingList(string $email, string $mailingList): int
    {
        $response = $this->put($this->config->api_key, "v3/lists/{$mailingList}/members/{$email}", [
            'subscribed' => 0,
        ]);

        return $response->status();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAllWebhooks(): ?array
    {
        $response = $this->get(
            $this->config->secret,
            'v3/domains/'.$this->config->domain.'/webhooks'
        );

        return $response->json('webhooks');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function send(array $data): ?array
    {
        $response = $this->post(
            password: $this->config->sending_api_key,
            endpoint: 'v3/'.$this->config->domain.'/messages',
            payload: array_merge($this->defaultSendData(), $data)
        );

        return $response->json();
    }

    protected function http(string $password): PendingRequest
    {
        return Http::baseUrl($this->config->endpoint)
            ->withBasicAuth('api', $password)
            ->acceptJson();
    }

    protected function get(string $password, string $endpoint, array $filters = []): PromiseInterface|HttpResponse
    {
        $response = $this->http($password)
            ->get($endpoint, $filters);

        $this->log($response);

        return $response;
    }

    /** @throws ConnectionException */
    protected function post(string $password, string $endpoint, array $payload = []): PromiseInterface|HttpResponse
    {
        $response = $this->http($password)
            ->asMultipart()
            ->post($endpoint, $payload);

        $this->log($response);

        return $response;
    }

    protected function put(string $password, string $endpoint, array $payload = []): PromiseInterface|HttpResponse
    {
        $response = $this->http($password)
            ->asMultipart()
            ->put($endpoint, $payload);

        $this->log($response);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSendData(): array
    {
        return [
            'from' => config('app.name').' <'.config('mail.from.address').'>',
            'to' => $this->config->subscribers_list,
            'h:Reply-To' => config('app.name').' <'.config('mail.from.address').'>',
            'o:tracking' => true,
        ];
    }

    protected function log(HttpResponse $response): void
    {
        app(RequestLogger::class)->handle(
            message: 'MailgunClient',
            response: $response,
            replace: [
                $this->config->secret => '{{MAILGUN_SECRET}}',
                $this->config->sending_api_key => '{{MAILGUN_SENDING_API_KEY}}',
                $this->config->api_key => '{{MAILGUN_API_KEY}}',
            ],
        );
    }
}
