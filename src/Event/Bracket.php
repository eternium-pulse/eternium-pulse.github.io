<?php

namespace Eternium\Event;

abstract class Bracket extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'bracket';

    protected function __construct(
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = ''
    ) {
        $this->withMages($mages);
        $this->withWarriors($warriors);
        $this->withBountyHunters($bounty_hunters);
    }

    public static function createContender(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new Bracket\Contender($mages, $warriors, $bounty_hunters);
    }

    public static function createVeteran(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new Bracket\Veteran($mages, $warriors, $bounty_hunters);
    }

    public static function createMaster(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new Bracket\Master($mages, $warriors, $bounty_hunters);
    }

    /**
     * @return Event[]
     */
    public function getPath(): array
    {
        return [...$this->parent->getPath(), $this];
    }
}
