<?php

namespace Eternium\Event;

final class Stats implements \Countable
{
    public int $length = 0;
    public int $maxLevel = 0;
    public int $maxTrial = 0;

    public function count(): int
    {
        return $this->length;
    }

    public function add(int $length, int $level, int $trial): void
    {
        $this->length += $length;
        $this->maxLevel = max($level, $this->maxLevel);
        $this->maxTrial = max($trial, $this->maxTrial);
    }

    public function aggregate(self $stats): void
    {
        $this->add($stats->length, $stats->maxLevel, $stats->maxTrial);
    }
}
