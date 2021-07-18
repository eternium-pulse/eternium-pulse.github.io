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
        public string $season = '',
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = '',
    ) {
        assert(0 < $index);

        $this->type = 'Season';

        parent::__construct("season-{$index}");

        $this->withMages($mages);
        $this->withWarriors($warriors);
        $this->withBountyHunters($bounty_hunters);
    }

    public static function create(
        int $index,
        string $season = '',
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = '',
    ): self {
        return new self($index, $season, $mages, $warriors, $bounty_hunters);
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
