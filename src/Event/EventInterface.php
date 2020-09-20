<?php

namespace Eternium\Event;

interface EventInterface
{
    public function __toString(): string;

    public function getName(bool $long = false): string;

    public function getType(bool $long = false): string;

    public function getTitle(bool $long = false): string;

    public function setParent(self $parent): self;

    /**
     * @return array<int, self>
     */
    public function getAncestors(): array;

    public function getPath(string $separator): string;

    /**
     * @template TReturn
     *
     * @param \Generator<void, void, ?self, TReturn> $handler
     */
    public function walk(\Generator $handler): void;
}
