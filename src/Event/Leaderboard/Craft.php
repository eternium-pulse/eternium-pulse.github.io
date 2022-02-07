<?php

namespace Eternium\Event\Leaderboard;

final class Craft implements \Stringable
{
    /**
     * @var int[]
     */
    public array $h = [0, 0, 0, 0, 0, 0, 0];

    public function __construct(int ...$h)
    {
        assert(count($h) <= count($this->h));
        assert(
            array_reduce($h, static fn (bool $carry, int $x): bool => $carry && ($x >= 0), true),
            'assert that $h elements are non-negative',
        );

        $this->h = array_replace($this->h, $h);
    }

    public function __toString(): string
    {
        return join('/', $this->h);
    }

    public static function from(string $s): self
    {
        return new self(...array_map('intval', explode('/', $s)));
    }
}
