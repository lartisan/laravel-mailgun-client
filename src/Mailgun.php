<?php

namespace Lartisan\MailgunClient;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Lartisan\MailgunClient\Client\MailgunClient;
use Lartisan\MailgunClient\ValueObjects\MailingList;

class Mailgun
{
    public function __construct(
        protected MailgunClient $client,
    ) {}

    /**
     * @return Collection<int, MailingList>|null
     */
    public function fetchMailingLists(): ?object
    {
        return $this->client->fetchMailingLists();
    }

    /** @throws RequestException|ConnectionException */
    public function addMemberToMailingList(string $email, string $mailingList): mixed
    {
        return $this->client->addMemberToMailingList($email, $mailingList);
    }

    /** @throws RequestException */
    public function subscribeMemberToMailingList(string $email, string $mailingList): mixed
    {
        return $this->client->subscribeMemberToMailingList($email, $mailingList);
    }

    public function unsubscribeMemberFromMailingList(string $email, string $mailingList): int
    {
        return $this->client->unsubscribeMemberFromMailingList($email, $mailingList);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAllWebhooks(): ?array
    {
        return $this->client->getAllWebhooks();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function send(array $data): ?array
    {
        return $this->client->send($data);
    }

    public function sendNewsletter(string $to, string $subject, string $html): void
    {
        $this->client->send([
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
        ]);
    }
}
