<?php

namespace Eternium\Event\Leaderboard;

final class Hero
{
    public string $name;
    public string $title;

    public function __construct(string $name, string $title = '')
    {
        assert('' !== $name);

        $this->name = $name;
        $this->title = $title;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
