<?php

namespace Lartisan\MailgunClient\Logging;

use GuzzleHttp\TransferStats;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use JsonException;
use Psr\Log\LoggerInterface;
use Throwable;

class RequestLogger
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {}

    /** @throws JsonException */
    public function handle(string $message, Response $response, array $replace = []): void
    {
        $message = Str::of($this->buildMessage($message, $response));

        foreach ($replace as $search => $replacement) {
            $message = $message->replace($search, $replacement);
        }

        if (isset($response->transferStats) && $response->transferStats instanceof TransferStats) {
            if ($response->transferStats->getTransferTime() > 10) {
                $this->logger->warning((string) $message);

                return;
            }
        }

        $this->logger->debug((string) $message);
    }

    protected function buildMessage(string $message, Response $response): string
    {
        $requestBody = $this->prettyPrint($response->transferStats->getRequest()->getBody());

        $output = 'RequestLogger: '.$message.PHP_EOL
            .'Request: '.$response->transferStats->getRequest()->getMethod().' '.$response->transferStats->getRequest()->getUri().PHP_EOL
            .'Headers: '.$this->prettyPrint(json_encode($response->transferStats->getRequest()->getHeaders(), JSON_THROW_ON_ERROR)).PHP_EOL
            .'Body: '.PHP_EOL.$requestBody.PHP_EOL.PHP_EOL
            .'Response: '.$response->status().PHP_EOL;

        $responseBody = $this->prettyPrint($response->body());

        return $output
            .'Headers: '.$this->prettyPrint(json_encode($response->headers(), JSON_THROW_ON_ERROR)).PHP_EOL
            .'Body: '.PHP_EOL.$responseBody.PHP_EOL;
    }

    protected function prettyPrint(?string $text): string
    {
        if (is_null($text)) {
            return '';
        }

        return $this->prettyPrintJson($text);
    }

    protected function prettyPrintJson(?string $json): string
    {
        if (is_null($json)) {
            return '';
        }

        try {
            return json_encode(
                json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (Throwable) {
            return $json;
        }
    }
}
