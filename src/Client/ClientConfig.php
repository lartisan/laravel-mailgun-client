<?php

namespace Lartisan\MailgunClient\Client;

readonly class ClientConfig
{
    public function __construct(
        public string $domain,
        public string $secret,
        public string $endpoint,
        public string $sending_api_key,
        public string $api_key,
        public string $subscribers_list,
    ) {}
}
