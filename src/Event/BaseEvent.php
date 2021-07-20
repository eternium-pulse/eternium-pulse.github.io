<?php

namespace Eternium\Event;

/**
 * @deprecated
 *
 * @implements \IteratorAggregate<string, Event>
 */
abstract class BaseEvent extends Event implements \IteratorAggregate
{
    /**
     * @return \Iterator<string, TEvent>
     */
    public function getIterator(): \Iterator
    {
        yield from [];
    }
}
