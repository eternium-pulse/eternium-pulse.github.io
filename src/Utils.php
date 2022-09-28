<?php

namespace Eternium;

use Eternium\Utils\Page;
use Eternium\Utils\Range;

abstract class Utils
{
    private const CSV_SEPARATOR = ';';

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
    public static function createNullReader(): \Generator
    {
        return 0;

        yield;
    }

    /**
     * @return \Generator<int, array, void, int>
     */
    public static function createCsvReader(string $file, array|false &$header = false): \Generator
    {
        $stream = @fopen($file, 'r');
        if (false === $stream) {
            throw self::getLastError() ?? new \RuntimeException("Unable to open '{$file}' for reading");
        }
        if (!flock($stream, LOCK_SH)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to acquire shared lock on '{$file}'");
        }

        if (false !== $header) {
            $header = fgetcsv($stream, 1024, self::CSV_SEPARATOR);
        }

        $rows = 0;
        while (false !== ($data = fgetcsv($stream, 1024, self::CSV_SEPARATOR))) {
            if ($header) {
                yield $data;
            }
            ++$rows;
        }

        if (!flock($stream, LOCK_UN)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to release lock on '{$file}'");
        }

        return $rows;
    }

    /**
     * @return \Generator<void, void, ?array, int>
     */
    public static function createCsvWriter(string $file, array|false $header = false): \Generator
    {
        $memory = @fopen('php://memory', 'r+');
        if (false === $memory) {
            throw self::getLastError() ?? new \RuntimeException('Unable to open in-memory stream');
        }

        if (false !== $header) {
            fputcsv($memory, $header, self::CSV_SEPARATOR);
        }

        $rows = 0;
        while (null !== ($data = yield)) {
            if (is_array($data)) {
                fputcsv($memory, $data, self::CSV_SEPARATOR);
                ++$rows;
            }
        }

        rewind($memory);
        self::dump($file, $memory);

        return $rows;
    }

    public static function read(string $file): string
    {
        $data = @file_get_contents($file);
        if (false === $data) {
            throw self::getLastError() ?? new \RuntimeException("Unable to open '{$file}' for reading");
        }

        return $data;
    }

    /**
     * @param resource|string $content
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

    /**
     * @return \Generator<int, Page, void, int>
     */
    public static function paginate(int $items, int $itemsPerPage, int $pagesLimit = PHP_INT_SIZE): \Generator
    {
        assert($items >= 0);
        assert($itemsPerPage > 0);
        assert($pagesLimit > 0);

        $length = min(Page::getLength($items, $itemsPerPage), $pagesLimit);
        $index0 = 0;
        $offset0 = 0;
        while ($index0 < $length) {
            $page = new Page(++$index0, $length);
            $page->range = new Range($offset0 + 1, min($items - $offset0, $itemsPerPage));

            yield $page;
            $offset0 += $itemsPerPage;
        }

        return $length;
    }

    public static function pack(string $binstr): string
    {
        return strtr(base64_encode($binstr), ['+' => '-', '/' => '_', '=' => '']);
    }

    public static function unpack(string $data): string
    {
        $binstr = base64_decode(strtr($data, '-_', '+/'), true);
        if (false === $binstr) {
            throw new \UnexpectedValueException('Malformed base64 encoding');
        }

        return $binstr;
    }
}
