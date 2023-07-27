<?php

declare(strict_types=1);

namespace Eternium\Sitemap;

use League\Uri\Contracts\UriInterface;

final class Url implements \Stringable
{
    public function __construct(
        public readonly UriInterface $loc,
        public readonly ?\DateTimeInterface $lastmod = null,
        public readonly ?Changefreq $changefreq = null,
        public readonly float $priority = 0.5,
    ) {
        \assert('http' === $loc->getScheme() || 'https' === $loc->getScheme());
        \assert(0 <= $priority && $priority <= 1);
    }

    public function __toString(): string
    {
        return $this->loc->__toString();
    }
}
