<?php

declare(strict_types=1);

namespace Eternium\Event;

final class SurviveTheLand extends BaseEvent
{
    use LeaderboardAwareTrait;

    public string $type = 'stl';

    private function __construct(public int $index)
    {
        $this->slug = "stl-{$index}";
        $this->name = "StL {$index}";
    }

    public static function create(int $index): self
    {
        return new self($index);
    }
}
