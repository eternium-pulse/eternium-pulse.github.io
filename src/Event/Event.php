<?php

namespace Eternium\Event;

abstract class Event implements \Stringable
{
    public protected(set) string $type;

    public protected(set) string $slug;

    public protected(set) string $name;

    /**
     * @deprecated
     */
    public protected(set) self $parent;

    final public function __toString(): string
    {
        return $this->slug;
    }

    /**
     * @return Event[]
     */
    public function getPath(): array
    {
        return [$this];
    }

    final public function walk(\Generator $handler): void
    {
        if (\is_iterable($this)) {
            foreach ($this as $event) {
                $event->walk($handler);
            }
        }
        $handler->send($this);
    }
}
