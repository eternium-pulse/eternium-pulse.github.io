<?php

namespace Eternium\Event;

interface EventInterface
{
    public function __toString(): string;

    /**
     * @param callable(Leaderboard, string): void $fetcher
     */
    public function fetch(callable $fetcher, string $prefix = ''): void;
}
