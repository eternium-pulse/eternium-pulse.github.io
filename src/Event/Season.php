<?php

namespace Eternium\Event;

/**
 * @extends BaseEvent<Leaderboard>
 */
final class Season extends BaseEvent
{
    use LeaderboardAwareTrait;
    use DateAwareTrait;

    public int $index;

    protected function __construct(int $index, Leaderboard ...$leaderboards)
    {
        assert(0 < $index);

        $this->index = $index;
        $this->type = 'Season';

        parent::__construct("season-{$index}", ...$leaderboards);
    }

    public static function create(int $index, Leaderboard ...$leaderboards): self
    {
        return new self($index, ...$leaderboards);
    }

    public function getName(bool $long = false): string
    {
        return "{$this->getType()} {$this->index}";
    }

    public function getType(bool $long = false): string
    {
        return 'Season';
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->getType()} {$this->index}";
    }
}
