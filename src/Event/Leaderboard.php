<?php

namespace Eternium\Event;

final class Leaderboard implements EventInterface
{
    private string $name;

    private string $id;

    private function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function mage(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public static function warrior(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public static function bountyhunter(string $id): self
    {
        return new self(__FUNCTION__, $id);
    }

    public function fetch(callable $fetcher, string $prefix): void
    {
        $fetcher("{$prefix}.{$this}", $this->id);
    }
}
