<?php

namespace Eternium;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class Utils
{
    public static function getLastError(bool $clear = true): ?\ErrorException
    {
        $error = error_get_last();
        if (null === $error) {
            return null;
        }

        if ($clear) {
            error_clear_last();
        }

        return new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
    }

    public static function createCsvReader(string $file): \Generator
    {
        $stream = @fopen($file, 'r');
        if (false === $stream) {
            throw self::getLastError() ?? new \RuntimeException("Unable to open '{$file}' for reading");
        }
        if (!flock($stream, LOCK_SH)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to acquire shared lock on '{$file}'");
        }

        $rows = 0;
        while (is_array($data = fgetcsv($stream, 1024))) {
            yield $data;
            ++$rows;
        }

        if (!flock($stream, LOCK_UN)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to release lock on '{$file}'");
        }

        return $rows;
    }

    public static function createCsvWriter(string $file): \Generator
    {
        $memory = @fopen('php://memory', 'r+');
        if (false === $memory) {
            throw self::getLastError() ?? new \RuntimeException('Unable to open in-memory stream');
        }

        $rows = 0;
        while (is_array($data = yield)) {
            fputcsv($memory, $data);
            ++$rows;
        }

        rewind($memory);
        self::dump($file, $memory);

        return $rows;
    }

    /**
     * @var resource|string
     *
     * @param mixed $content
     */
    public static function dump(string $file, $content): int
    {
        $path = dirname($file);
        if (!is_dir($path) && false === @mkdir($path, 0777, true)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to make directory '{$path}'");
        }

        $bytes = @file_put_contents($file, $content, LOCK_EX);
        if (false === $bytes) {
            throw self::getLastError() ?? new \RuntimeException("Unable to open '{$file}' for writing");
        }

        return $bytes;
    }

    public static function createHttpClient(string $apiKey): HttpClientInterface
    {
        assert('' !== $apiKey);

        return HttpClient::createForBaseUri('https://mfp.makingfun.com/api/', [
            'http_version' => '1.1',
            'max_redirects' => 0,
            'headers' => [
                'Accept' => 'application/json',
                'X-API-Key' => $apiKey,
            ],
        ]);
    }

    public static function createLeaderboardReader(HttpClientInterface $client, string $id): \Generator
    {
        assert(24 === strlen($id) && ctype_xdigit($id));

        $uri = "leaderboards/{$id}/rankings";
        $query = [
            'page' => 1,
            'pageSize' => 1000,
            'payload' => 'name,champion_level,hero.selectedPlayerNameID,trialStats.heroDeaths',
        ];

        $entries = 0;
        do {
            $pageSize = 0;
            $response = $client->request('GET', $uri, ['query' => $query]);
            foreach ($response->toArray() as $entry) {
                yield [
                    $entry['payload']['name'],
                    ucwords(strtr($entry['payload']['hero']['selectedPlayerNameID'] ?? '', '_', ' ')),
                    $entry['payload']['champion_level'],
                    $entry['score'],
                    $entry['payload']['trialStats']['heroDeaths'],
                ];
                ++$entries;
                ++$pageSize;
            }
            ++$query['page'];
        } while ($pageSize === $query['pageSize']);

        return $entries;
    }
}
