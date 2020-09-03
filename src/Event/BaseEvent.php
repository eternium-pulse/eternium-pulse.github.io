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
        return $this->toString();
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
     * @return \Iterator<int, EventInterface>
     */
    final public function getIterator(): \Iterator
    {
        yield from $this->events;
    }

    public function apply(callable $handler, string ...$prefix): array
    {
        array_push($prefix, ...$this->getPrefix());
        $data = [];
        foreach ($this as $event) {
            $data[(string) $event] = $event->apply($handler, ...$prefix);
        }

        return $data;
    }

    protected function getPrefix(): array
    {
        return [$this->name];
    }
}
