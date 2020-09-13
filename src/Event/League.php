<?php

namespace Eternium\Event;

final class League extends BaseEvent
{
    protected function __construct(string $name, Leaderboard ...$leaderboards)
    {
        $this->type = 'league';
        parent::__construct($name, ...$leaderboards);
    }

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
        $title = $this->name;
        if ($long) {
            $title = "{$title} {$this->type}";
        }

        return ucwords(strtr($title, '-', ' '));
    }
}
