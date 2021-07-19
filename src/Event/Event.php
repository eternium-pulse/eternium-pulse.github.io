<?php

namespace Eternium\Event;

abstract class Event implements \Stringable
{
    public string $type;

    public string $slug;

    public string $name;

    /**
     * @deprecated
     */
    public self $parent;

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

    /**
     * @deprecated
     */
    final public function walk(\Generator $handler): void
    {
        if (is_iterable($this)) {
            foreach ($this as $event) {
                $event->walk($handler);
            }
        }
        $handler->send($this);
    }
}
