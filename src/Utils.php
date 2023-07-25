<?php

namespace Eternium;

use Eternium\Utils\Page;
use Eternium\Utils\Range;

abstract class Utils
{
    private const CSV_MAX_LINE_LENGTH = 1024;
    private const CSV_CONTROL = [
        'separator' => ';',
    ];

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
    public static function createCsvReader(\SplFileInfo $file, array|false &$header = false): \Generator
    {
        $stream = $file->openFile();
        if (!$stream->flock(\LOCK_SH | \LOCK_NB)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to acquire shared lock on '{$file}'");
        }

        $stream->setFlags(\SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $stream->setCsvControl(...self::CSV_CONTROL);
        $stream->setMaxLineLen(self::CSV_MAX_LINE_LENGTH);

        $header = $stream->fgetcsv();

        $rows = 0;
        while (!$stream->eof()) {
            $data = $stream->fgetcsv();
            if (false !== $data) {
                yield $data;
            }
            ++$rows;
        }

        $stream->flock(\LOCK_UN);

        return $rows;
    }

    /**
     * @return \Generator<void, void, ?array, int>
     */
    public static function createCsvWriter(\SplFileInfo $file, array|false $header = false): \Generator
    {
        $path = $file->getPathInfo();
        if (!$path->isDir() && !@\mkdir($path, recursive: true)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to make directory '{$path}'");
        }

        $stream = $file->openFile('c');
        if (!$stream->flock(\LOCK_EX | \LOCK_NB)) {
            throw self::getLastError() ?? new \RuntimeException("Unable to acquire exclusive lock on '{$file}'");
        }

        $stream->setCsvControl(...self::CSV_CONTROL);
        $stream->setMaxLineLen(self::CSV_MAX_LINE_LENGTH);

        $stream->ftruncate(0);
        if (false !== $header) {
            $stream->fputcsv($header);
        }

        $rows = 0;
        while (\is_array($data = yield)) {
            $stream->fputcsv($data);
            ++$rows;
        }

        $stream->fflush();
        $stream->flock(\LOCK_UN);

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
        if (!is_dir($path) && false === @mkdir($path, recursive: true)) {
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
