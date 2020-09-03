<?php

namespace Eternium\Event;

/**
 * @implements \IteratorAggregate<int, EventInterface>
 */
abstract class BaseEvent implements EventInterface, \IteratorAggregate
{
    private string $name;

    /**
     * @var array<int, EventInterface>
     */
    private array $events;

    protected function __construct(string $name, EventInterface ...$events)
    {
        \assert(0 !== count($events));

        $this->name = $name;
        $this->events = $events;
    }

    final public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return \Iterator<int, EventInterface>
     */
    final public function getIterator(): \Iterator
    {
        yield from $this->events;
    }

    public function fetch(callable $fetcher, string $prefix = ''): void
    {
        if ('' !== $prefix) {
            $prefix .= '.';
        }
        $prefix .= $this;

        foreach ($this as $event) {
            $event->fetch($fetcher, $prefix);
        }
    }
}
