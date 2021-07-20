<?php

namespace Eternium\Event;

final class League extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'League';

    protected function __construct(
        public string $slug,
        string $mages = '',
        string $warriors = '',
        string $bounty_hunters = ''
    ) {
        $this->name = match ($slug) {
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
        };

        $this->withMages($mages);
        $this->withWarriors($warriors);
        $this->withBountyHunters($bounty_hunters);
    }

    public static function createBronze(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('bronze', $mages, $warriors, $bounty_hunters);
    }

    public static function createSilver(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('silver', $mages, $warriors, $bounty_hunters);
    }

    public static function createGold(string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        return new self('gold', $mages, $warriors, $bounty_hunters);
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
