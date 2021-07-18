<?php

namespace Eternium\Event;

/**
 * @extends BaseEvent<Leaderboard>
 */
final class Season extends BaseEvent
{
    use LeaderboardAwareTrait;

    protected function __construct(
        public int $index,
        public string $description = '',
        Leaderboard ...$leaderboards,
    ) {
        assert(0 < $index);

        $this->type = 'Season';

        parent::__construct("season-{$index}", ...$leaderboards);
    }

    public static function create(int $index, string $description = '', Leaderboard ...$leaderboards): self
    {
        return new self($index, $description, ...$leaderboards);
    }

    public function getName(bool $long = false): string
    {
        return "{$this->type} {$this->index}";
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->type} {$this->index}";
    }
}
