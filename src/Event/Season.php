<?php

namespace Eternium\Event;

final class Season extends BaseEvent
{
    public static function n(int $n, Leaderboard ...$leaderboards): self
    {
        \assert(0 < $n);

        return new self("season.{$n}", ...$leaderboards);
    }
}
