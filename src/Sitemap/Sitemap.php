<?php

declare(strict_types=1);

namespace Eternium\Sitemap;

use League\Uri\Contracts\UriInterface;

/**
 * @implements \IteratorAggregate<int, Url>
 */
final class Sitemap implements \Countable, \IteratorAggregate
{
    /**
     * @var Url[]
     */
    private array $urlset = [];

    public function count(): int
    {
        return \count($this->urlset);
    }

    /**
     * @return \Iterator<int, Url>
     */
    public function getIterator(): \Iterator
    {
        yield from $this->urlset;
    }

    public function add(
        UriInterface $loc,
        ?\DateTimeInterface $lastmod = null,
        ?Changefreq $changefreq = null,
        float $priority = Url::DEFAULT_PRIORITY,
    ): self {
        $this->urlset[] = new Url($loc, $lastmod, $changefreq, $priority);

        return $this;
    }

    public function addUrl(Url ...$urlset): self
    {
        \array_push($this->urlset, ...$urlset);

        return $this;
    }
}
