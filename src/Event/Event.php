<?php

namespace Eternium\Event;

final class Event extends BaseEvent
{
    protected function __construct(string $type, int $id, EventInterface ...$events)
    {
        assert(0 < $id);

        parent::__construct("{$type}-{$id}", ...$events);
    }

    public static function season(int $id, Leaderboard ...$leaderboards): self
    {
        return new self('season', $id, ...$leaderboards);
    }

    public static function anb(int $id, League ...$leagues): self
    {
        return new self('anb', $id, ...$leagues);
    }

    public function getType(): string
    {
        return strstr($this->getName(), '-', true);
    }
}
