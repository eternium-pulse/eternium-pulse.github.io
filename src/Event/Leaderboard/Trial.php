<?php

namespace Eternium\Event\Leaderboard;

final class Trial
{
    public int $level;
    public int $time;
    public int $deaths;

    public function __construct(int $level, int $time, int $deaths)
    {
        assert(0 < $level);
        assert(0 < $time);
        assert(0 <= $deaths);

        $this->level = $level;
        $this->time = $time;
        $this->deaths = $deaths;
    }

    public static function fromScore(int $score, int $deaths): self
    {
        assert(10000 <= $score);

        return new self($score / 10000, 9999 - $score % 10000, $deaths);
    }
}
