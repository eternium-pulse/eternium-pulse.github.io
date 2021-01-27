<?php

namespace Eternium;

use Eternium\Utils\Page;
use Eternium\Utils\Range;

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
     * @return \Generator<void, void, ?array, int>
     */
    public static function createCsvWriter(string $file): \Generator
    {
        $memory = @fopen('php://memory', 'r+');
        if (false === $memory) {
            throw self::getLastError() ?? new \RuntimeException('Unable to open in-memory stream');
        }

        $rows = 0;
        while (null !== ($data = yield)) {
            if (is_array($data)) {
                fputcsv($memory, $data);
                ++$rows;
            }
        }

        rewind($memory);
        self::dump($file, $memory);

        return $rows;
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
    public static function paginate(int $items, int $itemsPerPage): \Generator
    {
        $length = Page::getLength($items, $itemsPerPage);
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
}
