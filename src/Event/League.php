<?php

namespace Eternium\Event;

final class League extends BaseEvent
{
    public static function bronze(Leaderboard ...$leaderboards): self
    {
        return new self('bronze', ...$leaderboards);
    }

    public static function silver(Leaderboard ...$leaderboards): self
    {
        return new self('silver', ...$leaderboards);
    }

    public static function gold(Leaderboard ...$leaderboards): self
    {
        return new self('gold', ...$leaderboards);
    }

    public function getType(): string
    {
        return 'league';
    }
}
