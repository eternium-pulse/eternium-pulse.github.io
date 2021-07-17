<?php

namespace Eternium\Event;

/**
 * @template TEvent of Event
 *
 * @deprecated
 *
 * @implements \IteratorAggregate<string, TEvent>
 */
abstract class BaseEvent extends Event implements \IteratorAggregate
{
    use EventTrait;

    /**
     * @var array<string, TEvent>
     */
    private array $events;

    protected function __construct(string $slug, Event ...$events)
    {
        $this->slug = $slug;
        foreach ($events as $event) {
            $this->withEvent($event);
        }
    }

    /**
     * @return \Iterator<string, TEvent>
     */
    final public function getIterator(): \Iterator
    {
        yield from $this->events;
    }

    public function walk(\Generator $handler): void
    {
        foreach ($this as $event) {
            $event->walk($handler);
        }
        $handler->send($this);
    }

    final protected function withEvent(Event $event): self
    {
        $event->parent = $this;
        $this->events[$event->__toString()] = $event;

        return $this;
    }
}
