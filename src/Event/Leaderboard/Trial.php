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
        assert(isset($trialStats['boss']['duration']));
        assert(isset($trialStats['hero']['duration']));
        assert(isset($trialStats['killsElite']));
        assert(isset($trialStats['killsNormal']));
        assert(isset($trialStats['heroDeaths']));

        $time = 9999 - $score % 10000;
        $bossTime = $trialStats['boss']['duration'];
        if ($bossTime > $time) {
            if ($bossTime < $trialStats['hero']['duration']) {
                $bossTime = (int) round($bossTime / $trialStats['hero']['duration'] * $time);
            } else {
                $bossTime = 0;
            }
        }
        $bossTime = max($bossTime, 0);

        return new self(
            (int) ($score / 10000),
            $time,
            $bossTime,
            $trialStats['killsElite'],
            $trialStats['killsNormal'],
            $trialStats['heroDeaths'],
        );
    }

    public static function formatTimePeriod(int $time): string
    {
        assert($time >= 0);

        return sprintf('%d:%02d', $time / 60, $time % 60);
    }

    public function formatTime(): string
    {
        return self::formatTimePeriod($this->time);
    }

    public function formatBossTime(): string
    {
        return self::formatTimePeriod($this->bossTime);
    }
}
