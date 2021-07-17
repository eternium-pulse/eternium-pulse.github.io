<?php

namespace Eternium\Event;

abstract class Event implements \Stringable
{
    public string $type;

    public string $slug;

    final public function __toString(): string
    {
        return $this->slug;
    }
}
