<?php

namespace Eternium\Event;

use Eternium\Event\Leaderboard\Entry;

/**
 * @implements \IteratorAggregate<int, Entry>
 */
final class Leaderboard implements EventInterface, \Countable, \IteratorAggregate
{
    private string $name;

    private string $id;

    /**
     * @var array<int, Entry>
     */
    private array $entries = [];

    private function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function mage(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public static function warrior(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public static function bountyhunter(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public function count(): int
    {
        return count($this->entries);
    }

    /**
     * @return \Iterator<int, Entry>
     */
    public function getIterator(): \Iterator
    {
        yield from $this->entries;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function rank(int $n): ?Entry
    {
        assert(0 < $n);

        return $this->entries[$n - 1] ?? null;
    }

    public function add(Entry ...$entries): void
    {
        assert(0 !== count($entries));

        array_push($this->entries, ...$entries);
    }

    public function fetch(callable $fetcher, string $prefix = ''): void
    {
        if ('' !== $prefix) {
            $prefix .= '.';
        }
        $prefix .= $this;

        $fetcher($this, $prefix);
    }
}
