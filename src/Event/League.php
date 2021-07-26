<?php

namespace Eternium\Event;

abstract class League extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'League';

    protected function __construct(
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = ''
    ) {
        $this->withMages($mages);
        $this->withWarriors($warriors);
        $this->withBountyHunters($bounty_hunters);
    }

    public static function createBronze(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new League\Bronze($mages, $warriors, $bounty_hunters);
    }

    public static function createSilver(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new League\Silver($mages, $warriors, $bounty_hunters);
    }

    public static function createGold(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new League\Gold($mages, $warriors, $bounty_hunters);
    }

    /**
     * @return Event[]
     */
    public function getPath(): array
    {
        return [...$this->parent->getPath(), $this];
    }
}
