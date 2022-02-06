<?php

namespace Eternium\Event\Leaderboard;

final class Trial
{
    public function __construct(
        public int $level,
        public int $time,
        public int $bossTime,
        public int $eliteKills,
        public int $trashKills,
        public int $deaths,
    ) {
        assert($level > 0);
        assert($time > 0);
        assert($bossTime >= 0);
        assert($eliteKills >= 0);
        assert($trashKills >= 0);
        assert($deaths >= 0);
    }

    public static function fromTrialStats(int $score, array $trialStats): self
    {
        assert($score >= 10000);
        assert(isset($trialStats['boss']['t0']));
        assert(isset($trialStats['killsElite']));
        assert(isset($trialStats['killsNormal']));
        assert(isset($trialStats['heroDeaths']));

        return new self(
            (int) ($score / 10000),
            $time = 9999 - $score % 10000,
            max($time - $trialStats['boss']['t0'], 0),
            $trialStats['killsElite'],
            $trialStats['killsNormal'],
            $trialStats['heroDeaths'],
        );
    }
}
