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

    final public function getPath(string $separator): string
    {
        $path = [$this];
        for ($e = $this; isset($e->parent); $e = $e->parent) {
            array_unshift($path, $e->parent);
        }

        return join($separator, $path);
    }
}
