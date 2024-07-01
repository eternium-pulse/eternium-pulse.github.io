<?php

declare(strict_types=1);

namespace Eternium\Sitemap;

use League\Uri\Contracts\UriInterface;

final readonly class Url implements \Stringable
{
    public const DEFAULT_PRIORITY = 0.5;

    public function __construct(
        public UriInterface $loc,
        public ?\DateTimeInterface $lastmod = null,
        public ?Changefreq $changefreq = null,
        public float $priority = self::DEFAULT_PRIORITY,
    ) {
        \assert('http' === $loc->getScheme() || 'https' === $loc->getScheme());
        \assert(0 <= $priority && $priority <= 1);
    }

    public function __toString(): string
    {
        return $this->loc->__toString();
    }
}
