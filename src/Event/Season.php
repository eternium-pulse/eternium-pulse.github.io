<?php

namespace Eternium\Event;

final class Season extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'season';

    protected function __construct(
        public int $index,
        public string $season = '',
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = '',
    ) {
        assert(0 < $index);

        $this->slug = "season-{$index}";
        $this->name = "Season {$index}";
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
}
