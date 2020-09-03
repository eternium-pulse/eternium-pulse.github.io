<?php

namespace Eternium\Event\Leaderboard;

/**
 * @property int           $trialLevel
 * @property \DateInterval $time
 */
final class Entry
{
    public string $name;

    public string $title;

    public int $championLevel;

    public int $score;

    public int $deaths;

    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return null;
    }

    public function trialLevel(): int
    {
        return $this->score / 10000;
    }

    public function time(): \DateInterval
    {
        $time = 9999 - $this->score % 10000;
        $m = (int) $time / 60;
        $s = $time % 60;

        return new \DateInterval("PT{$m}M{$s}S");
    }
}
