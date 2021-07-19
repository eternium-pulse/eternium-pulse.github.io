<?php

namespace Eternium\Event;

/**
 * @implements BaseEvent<Leaderboard>
 */
final class League extends BaseEvent
{
    use LeaderboardAwareTrait;

    protected function __construct(
        string $slug,
        string $name,
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = ''
    ) {
        $this->name = $name;
        $this->type = 'League';

        parent::__construct($slug);

        $this->withMages($mages);
        $this->withWarriors($warriors);
        $this->withBountyHunters($bounty_hunters);
    }

    public static function createBronze(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('bronze', 'Bronze', $mages, $warriors, $bounty_hunters);
    }

    public static function createSilver(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('silver', 'Silver', $mages, $warriors, $bounty_hunters);
    }

    public static function createGold(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('gold', 'Gold', $mages, $warriors, $bounty_hunters);
    }

    /**
     * @return Event[]
     */
    public function getPath(): array
    {
        return [...$this->parent->getPath(), $this];
    }

    public function getName(bool $long = false): string
    {
        return $this->name;
    }

    public function getTitle(bool $long = false): string
    {
        if ($long && isset($this->parent)) {
            return "{$this->parent->getName()} {$this->name} {$this->type}";
        }

        return "{$this->name} {$this->type}";
    }
}
