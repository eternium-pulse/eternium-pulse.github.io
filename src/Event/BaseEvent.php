<?php

namespace Eternium\Event;

/**
 * @implements \IteratorAggregate<string, EventInterface>
 */
abstract class BaseEvent implements EventInterface, \ArrayAccess, \IteratorAggregate
{
    use EventTrait;

    /**
     * @var array<string, EventInterface>
     */
    private array $events;

    protected function __construct(string $name, EventInterface ...$events)
    {
        \assert(0 !== count($events));

        $this->name = $name;
        $this->stats = new Stats();
        foreach ($events as $event) {
            $event->setParent($this);
            $this->events[$event->toString()] = $event;
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
     * @return ?EventInterface
     */
    public function offsetGet($name)
    {
        return $this->events[$name] ?? null;
    }

    /**
     * @param string         $name
     * @param EventInterface $event
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
     * @return \Iterator<string, EventInterface>
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
}
