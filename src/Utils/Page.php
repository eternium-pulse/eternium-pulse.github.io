<?php

namespace Eternium\Utils;

final class Page
{
    public int $index;
    public int $index0;
    public int $revindex;
    public int $revindex0;
    public bool $first;
    public bool $last;
    public int $length;

    public function __construct(int $index, int $length)
    {
        assert(0 < $length);
        assert(0 < $index && $index <= $length);

        $this->index = $index;
        $this->index0 = $index - 1;
        $this->revindex = $length - $this->index0;
        $this->revindex0 = $length - $this->index;
        $this->first = 1 === $index;
        $this->last = $length === $index;
        $this->length = $length;
    }

    public static function getPagesCount(int $entriesCount, int $entriesPerPage): int
    {
        assert(0 < $entriesPerPage);

        return ((int) ceil(max($entriesCount, 0) / $entriesPerPage)) ?: 1;
    }
}
