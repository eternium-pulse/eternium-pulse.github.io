<?php

namespace Eternium\Event;

interface EventInterface
{
    public function toString(): string;

    public function getName(): string;

    public function getTitle(bool $long = false): string;

    public function getType(): string;

    public function getStats(): Stats;

    public function walk(\Generator $handler, EventInterface ...$chain): void;
}
