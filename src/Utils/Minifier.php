<?php

namespace Eternium\Utils;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Minifier
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::createForBaseUri('https://www.toptal.com/developers/');
    }

    public function __invoke(string $type, string $code): string
    {
        return $this->tryMinify($type, $code);
    }

    /**
     * @param 'css'|'js' $type
     */
    public function minify(string $type, string $code): string
    {
        $uri = match ($type) {
            'css' => 'cssminifier/raw',
            'js' => 'javascript-minifier/raw',
            default => throw new \DomainException('Unsupported minification type'),
        };

        $options = [
            'body' => [
                'input' => $code,
            ],
        ];

        return $this->client->request('POST', $uri, $options)->getContent();
    }

    public function tryMinify(string $type, string $code): string
    {
        try {
            return $this->minify($type, $code);
        } catch (\DomainException) {
            return $code;
        }
    }
}
