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

    public function getTitle(bool $long = false): string
    {
        [$type, $id] = $this->parseName();
        if ('anb' === $type) {
            if ($long) {
                $name = 'A New Beginning';
            } else {
                $name = 'ANB';
            }
        } else {
            $name = ucwords(strtr($type, '-', ' '));
        }

        return "{$name} {$id}";
    }

    public function getType(): string
    {
        return $this->parseName()[0];
    }

    /**
     * @return array{string, int}
     */
    protected function parseName(): array
    {
        $name = $this->getName();
        $pos = strrpos($name, '-', -1);

        return [substr($name, 0, $pos), (int) substr($name, $pos + 1)];
    }
}
