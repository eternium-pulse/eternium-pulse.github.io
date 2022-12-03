<?php

declare(strict_types=1);

namespace Eternium\Utils;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Eternium\Utils\Url
 */
final class UrlTest extends TestCase
{
    /**
     * @testWith ["https://example.com", "", "https://example.com"]
     *           ["https://example.com", "/", "https://example.com/"]
     *           ["https://example.com/", "", "https://example.com/"]
     *           ["https://example.com/", "/", "https://example.com/"]
     *           ["https://example.com", "abc", "https://example.com/abc"]
     *           ["https://example.com", "abc/", "https://example.com/abc/"]
     *           ["https://example.com/abc", "def", "https://example.com/def"]
     *           ["https://example.com/abc/", "def", "https://example.com/abc/def"]
     *           ["https://example.com/abc/", "def/", "https://example.com/abc/def/"]
     *           ["https://example.com/abc/", "/def", "https://example.com/def"]
     *           ["https://example.com/abc/", "/def/", "https://example.com/def/"]
     *           ["https://example.com?foo=bar", "", "https://example.com"]
     *           ["https://example.com#foo", "", "https://example.com"]
     */
    public function testResolve(string $base, string $path, string $expected): void
    {
        $url = Url::parse($base)->resolve($path);
        $this->assertSame($expected, (string) $url);
    }
}
