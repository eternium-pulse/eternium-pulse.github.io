<?php

namespace Eternium\Event;

/**
 * @implements \IteratorAggregate<string, EventInterface>
 */
abstract class BaseEvent implements EventInterface, \ArrayAccess, \IteratorAggregate
{
    private string $name;

    private Stats $stats;

    /**
     * @var array<string, EventInterface>
     */
    private array $events;

    protected function __construct(string $name, EventInterface ...$events)
    {
        \assert(0 !== count($events));

        $this->name = $name;
        foreach ($events as $event) {
            $this->events[$event->getName()] = $event;
        }
        $this->stats = new Stats();
    }

    final public function __toString(): string
    {
        return $this->toString();
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

    public function toString(): string
    {
        return $this->name;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Iterator<string, EventInterface>
     */
    final public function getIterator(): \Iterator
    {
        yield from $this->events;
    }

    final public function getStats(): Stats
    {
        return $this->stats;
    }

    public function walk(\Generator $handler, EventInterface ...$chain): void
    {
        foreach ($this->getIterator() as $event) {
            $event->walk($handler, $this, ...$chain);
            $this->stats->aggregate($event->getStats());
        }
        $handler->send([$this, ...$chain]);
    }
}
