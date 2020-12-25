<?php

namespace Eternium\Utils;

/**
 * @implements \IteratorAggregate<int, int>
 */
class Range implements \Countable, \IteratorAggregate
{
    public int $offset;
    public int $offset0;
    public int $length;

    public function __construct(int $offset, int $length)
    {
        assert(0 < $offset);
        assert(0 <= $length);

        $this->offset = $offset;
        $this->offset0 = $offset - 1;
        $this->length = $length;
    }

    public function count(): int
    {
        return $this->length;
    }

    /**
     * @return \Iterator<int, int>
     */
    public function getIterator(): \Iterator
    {
        $end = $this->offset0 + $this->length;
        for ($i = $this->offset0; $i < $end; ++$i) {
            yield $i;
        }
    }

    /**
     * @template TValue
     *
     * @param array<int, TValue> $items
     *
     * @return iterable<int, TValue>
     */
    public function slice(array $items): iterable
    {
        foreach ($this as $i) {
            yield $i + 1 => $items[$i];
        }
    }

    /**
     * @template TValue
     *
     * @param array<int, TValue> $items
     *
     * @return iterable<int, TValue>
     */
    public function slice0(array $items): iterable
    {
        foreach ($this as $i) {
            yield $i => $items[$i];
        }
    }
}
