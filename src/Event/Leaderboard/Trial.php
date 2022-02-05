<?php

namespace Eternium\Event\Leaderboard;

final class Trial
{
    public function __construct(
        public int $level,
        public int $time,
        public int $bossTime,
        public int $deaths,
    ) {
        assert($level > 0);
        assert($time > 0);
        assert($bossTime >= 0);
        assert($deaths >= 0);
    }

    public static function fromScore(int $score, int $bossT0, int $deaths): self
    {
        assert($score >= 10000);

        return new self((int) ($score / 10000), $time = 9999 - $score % 10000, max($time - $bossT0, 0), $deaths);
    }
}
