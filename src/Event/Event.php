<?php

namespace Eternium\Event;

final class Event extends BaseEvent
{
    public int $index;

    protected function __construct(string $type, int $index, EventInterface ...$events)
    {
        assert(0 < $index);

        $this->index = $index;
        $this->type = $type;

        parent::__construct("{$type}-{$index}", ...$events);
    }

    public static function season(int $index, Leaderboard ...$leaderboards): self
    {
        return new self('season', $index, ...$leaderboards);
    }

    public static function anb(int $index, League ...$leagues): self
    {
        return new self('anb', $index, ...$leagues);
    }

    public function getName(bool $long = false): string
    {
        return "{$this->getType()} {$this->index}";
    }

    public function getType(bool $long = false): string
    {
        if ('anb' === $this->type) {
            if ($long) {
                return 'A New Beginning';
            }

            return 'ANB';
        }

        return ucfirst($this->type);
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->getType($long)} {$this->index}";
    }
}
