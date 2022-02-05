<?php

namespace Eternium\Event\Leaderboard;

final class Champion
{
    public function __construct(public int $level)
    {
        assert($level >= 0);
    }
}
