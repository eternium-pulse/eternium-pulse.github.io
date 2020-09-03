<?php

namespace Eternium\Event;

final class Event extends BaseEvent
{
    private string $type;

    protected function __construct(string $type, int $id, EventInterface ...$events)
    {
        assert(0 < $id);

        $this->type = $type;
        parent::__construct($id, ...$events);
    }

    public static function season(int $id, Leaderboard ...$leaderboards): self
    {
        return new self(__FUNCTION__, $id, ...$leaderboards);
    }

    public static function anb(int $id, League ...$leagues): self
    {
        return new self(__FUNCTION__, $id, ...$leagues);
    }

    public function getId(): int
    {
        return $this->getName();
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function getPrefix(): array
    {
        return [$this->type, $this->getName()];
    }
}
