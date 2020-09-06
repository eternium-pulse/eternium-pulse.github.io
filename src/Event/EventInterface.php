<?php

namespace Eternium\Event;

interface EventInterface
{
    public function toString(): string;

    public function getName(): string;

    public function getType(): string;

    /**
     * @param callable(Leaderboard, array<int, string>): array $handler
     */
    public function apply(callable $handler, string ...$prefix): array;

    public function walk(\Generator $handler, EventInterface ...$chain): void;
}
