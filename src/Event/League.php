<?php

namespace Eternium\Event;

/**
 * @implements BaseEvent<Leaderboard>
 */
final class League extends BaseEvent
{
    use LeaderboardAwareTrait;
    use DateAwareTrait;

    protected function __construct(string $slug, string $name, Leaderboard ...$leaderboards)
    {
        $this->name = $name;
        $this->type = 'League';

        parent::__construct($slug, ...$leaderboards);
    }

    public static function createBronze(Leaderboard ...$leaderboards): self
    {
        return new self('bronze', 'Bronze', ...$leaderboards);
    }

    public static function createSilver(Leaderboard ...$leaderboards): self
    {
        return new self('silver', 'Silver', ...$leaderboards);
    }

    public static function createGold(Leaderboard ...$leaderboards): self
    {
        return new self('gold', 'Gold', ...$leaderboards);
    }

    public function getName(bool $long = false): string
    {
        return $this->name;
    }

    public function getType(bool $long = false): string
    {
        return $this->type;
    }

    public function getTitle(bool $long = false): string
    {
        if ($long && isset($this->parent)) {
            return "{$this->parent->getName()} {$this->name} {$this->type}";
        }

        return "{$this->name} {$this->type}";
    }
}
