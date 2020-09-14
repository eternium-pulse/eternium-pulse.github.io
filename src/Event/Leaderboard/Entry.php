<?php

namespace Eternium\Event\Leaderboard;

final class Entry
{
    public Hero $hero;
    public Champion $champion;
    public Gear $gear;
    public Trial $trial;

    public function __construct(Hero $hero, Champion $champion, Gear $gear, Trial $trial)
    {
        $this->hero = $hero;
        $this->champion = $champion;
        $this->gear = $gear;
        $this->trial = $trial;
    }

    public function __toString(): string
    {
        return $this->hero->__toString();
    }

    public static function fromArray(array $data): self
    {
        assert(7 === count($data));

        return new self(
            new Hero($data[0], $data[1]),
            new Champion($data[2]),
            new Gear($data[3]),
            new Trial($data[4], $data[5], $data[6]),
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
        ];
    }
}
