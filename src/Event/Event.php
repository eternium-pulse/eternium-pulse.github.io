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

    public function getTitle(bool $long = false): string
    {
        if ('anb' === $this->type) {
            if ($long) {
                $name = 'A New Beginning';
            } else {
                $name = 'ANB';
            }
        } else {
            $name = ucwords(strtr($this->type, '-', ' '));
        }

        return "{$name} {$this->index}";
    }
}
