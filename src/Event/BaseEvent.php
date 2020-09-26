<?php

namespace Eternium\Event;

/**
 * @template TEvent of EventInterface
 *
 * @implements \IteratorAggregate<string, TEvent>
 */
abstract class BaseEvent implements EventInterface, \ArrayAccess, \IteratorAggregate
{
    use EventTrait;

    public \DateTimeInterface $start;
    public \DateTimeInterface $end;

    /**
     * @var array<string, TEvent>
     */
    private array $events;

    protected function __construct(string $name, EventInterface ...$events)
    {
        \assert(0 !== count($events));

        $this->name = $name;
        $this->stats = new Stats();
        foreach ($events as $event) {
            $event->setParent($this);
            $this->events[$event->__toString()] = $event;
        }
    }

    /**
     * @param string $name
     */
    public function offsetExists($name): bool
    {
        return isset($this->events[$name]);
    }

    /**
     * @param string $name
     *
     * @return ?TEvent
     */
    public function offsetGet($name)
    {
        return $this->events[$name] ?? null;
    }

    /**
     * @param string $name
     * @param TEvent $event
     */
    public function offsetSet($name, $event)
    {
        throw new \BadMethodCallException();
    }

    /**
     * @param string $name
     */
    public function offsetUnset($name)
    {
        throw new \BadMethodCallException();
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
            $this->stats->aggregate($event->stats);
        }
        $handler->send($this);
    }

    final public function startOn(\DateTimeInterface $date): self
    {
        $this->start = $date;

        return $this;
    }

    final public function endOn(\DateTimeInterface $date): self
    {
        $this->end = $date;

        return $this;
    }
}
