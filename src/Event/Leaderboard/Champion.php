<?php

namespace Eternium\Event\Leaderboard;

final class Champion
{
    public int $level;

    public function __construct(int $level)
    {
        assert(0 <= $level);

        $this->level = $level;
    }
}
