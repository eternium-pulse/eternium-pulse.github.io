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

    public function getTitle(bool $long = false): string
    {
        $title = $this->getName();
        if ($long) {
            $title = "{$title} {$this->getType()}";
        }

        return ucwords(strtr($title, '-', ' '));
    }

    public function getType(): string
    {
        return 'league';
    }
}
