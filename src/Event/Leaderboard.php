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
        return $this->toString();
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return 'leaderboard';
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function apply(callable $handler, string ...$prefix): array
    {
        return $handler($this, ...[...$prefix, $this->name]);
    }
}
