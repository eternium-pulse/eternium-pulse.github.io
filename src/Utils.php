<?php

namespace Eternium;

use Eternium\Utils\Page;
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

    /**
     * @return \Generator<int, array, void, int>
     */
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

    /**
     * @return \Generator<int, void, ?array, int>
     */
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

    public static function createHttpClient(string $apiKey = ETERNIUM_API_KEY): HttpClientInterface
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

    /**
     * @return \Generator<int, Page, void, int>
     */
    public static function paginate(int $n, int $pageSize): \Generator
    {
        $page = new Page(1, Page::getPagesCount($n, $pageSize));
        while (!$page->last) {
            yield $page;
            $page = new Page($page->index + 1, $page->length);
        }
        yield $page;

        return $page->length;
    }
}
