<?php

namespace Eternium\Event\Leaderboard;

final class Entry
{
    public function __construct(
        public Hero $hero,
        public Champion $champion,
        public Gear $gear,
        public Trial $trial,
        public string $platform = '',
    ) {
    }

    public function __toString(): string
    {
        return $this->hero->__toString();
    }

    public static function fromArray(array $data): self
    {
        assert(count($data) >= 7);

        return new self(
            new Hero($data[0], $data[1]),
            new Champion($data[2]),
            new Gear($data[3]),
            new Trial($data[4], $data[5], $data[6]),
            $data[7] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            $this->hero->name,
            $this->hero->title,
            $this->champion->level,
            $this->gear->averageLevel,
            $this->trial->level,
            $this->trial->time,
            $this->trial->deaths,
            $this->platform,
        ];
    }
}
