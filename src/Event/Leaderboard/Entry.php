<?php

namespace Eternium\Event\Leaderboard;

final class Entry implements \JsonSerializable
{
    public string $name;
    public string $title;
    public int $level;
    public int $trial;
    public int $time;
    public int $deaths;

    public function __construct(string $name, string $title, int $level, int $score, int $deaths)
    {
        assert('' !== $name);
        assert(0 <= $level);
        assert(0 <= $deaths);

        $this->name = $name;
        $this->title = $title;
        $this->level = $level;
        $this->setScore($score);
        $this->deaths = $deaths;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): array
    {
        $data['name'] = $this->name;
        if ('' !== $this->title) {
            $data['title'] = $this->title;
        }
        $data['level'] = $this->level;
        $data['score'] = $this->getScore();
        $data['deaths'] = $this->deaths;

        return $data;
    }

    public function toArray(): array
    {
        return [$this->name, $this->title, $this->level, $this->getScore(), $this->deaths];
    }

    public function getScore(): int
    {
        return $this->trial * 10000 + 9999 - $this->time;
    }

    public function setScore(int $score): void
    {
        assert(10000 <= $score);

        $this->trial = $score / 10000;
        $this->time = 9999 - $score % 10000;
    }
}
