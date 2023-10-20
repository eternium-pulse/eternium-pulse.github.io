<?php

namespace Eternium\Event\Leaderboard;

final class Entry implements \Stringable
{
    public const HEADER = [
        'Name',
        'Title',
        'ChampLv',
        'AvgItemLv',
        'TrialLv',
        'TrialTime',
        'BossTime',
        'EliteKills',
        'TrashKills',
        'Deaths',
        'Platform',
    ];

    public function __construct(
        public Hero $hero,
        public Trial $trial,
        public string $platform = '',
    ) {}

    public function __toString(): string
    {
        return $this->hero->__toString();
    }

    public static function fromArray(array $data): self
    {
        assert(count($data) === count(self::HEADER));

        return new self(
            new Hero($data[0], $data[1], $data[2], (float) strtr($data[3], ',', '.')),
            new Trial($data[4], $data[5], $data[6], $data[7], $data[8], $data[9]),
            $data[10],
        );
    }

    public function toArray(): array
    {
        return [
            $this->hero->name,
            $this->hero->title,
            $this->hero->championLevel,
            number_format($this->hero->averageItemLevel, 2, '.'),
            $this->trial->level,
            $this->trial->time,
            $this->trial->bossTime,
            $this->trial->eliteKills,
            $this->trial->trashKills,
            $this->trial->deaths,
            $this->platform,
        ];
    }
}
