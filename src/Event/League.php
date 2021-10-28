<?php

namespace Eternium\Event;

abstract class League extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'league';

    protected function __construct(
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = '',
        array ...$brackets,
    ) {
        if ($brackets) {
            foreach ($brackets as $bracket => $ids) {
                $this->withBracket($bracket, ...$ids);
            }
        } else {
            $this->withBracket('', $mages, $warriors, $bounty_hunters);
        }
    }

    public static function createBronze(string $mages = '', string $warriors = '', string $bounty_hunters = '', array $brackets = []): self
    {
        return new League\Bronze($mages, $warriors, $bounty_hunters, ...$brackets);
    }

    public static function createSilver(string $mages = '', string $warriors = '', string $bounty_hunters = '', array $brackets = []): self
    {
        return new League\Silver($mages, $warriors, $bounty_hunters, ...$brackets);
    }

    public static function createGold(string $mages = '', string $warriors = '', string $bounty_hunters = '', array $brackets = []): self
    {
        return new League\Gold($mages, $warriors, $bounty_hunters, ...$brackets);
    }

    /**
     * @return Event[]
     */
    public function getPath(): array
    {
        return [...$this->parent->getPath(), $this];
    }
}
