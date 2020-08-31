<?php

namespace Eternium\Event;

interface EventInterface
{
    public function __toString(): string;

    public function fetch(callable $fetcher, string $prefix): void;
}
