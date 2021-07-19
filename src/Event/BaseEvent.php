<?php

namespace Eternium\Event;

/**
 * @deprecated
 *
 * @implements \IteratorAggregate<string, Event>
 */
abstract class BaseEvent extends Event implements \IteratorAggregate
{
    protected function __construct(string $slug, Event ...$events)
    {
        $this->slug = $slug;
    }

    /**
     * @return \Iterator<string, TEvent>
     */
    public function getIterator(): \Iterator
    {
        yield from [];
    }

    public function walk(\Generator $handler): void
    {
        foreach ($this as $event) {
            $event->walk($handler);
        }
        $handler->send($this);
    }
}
