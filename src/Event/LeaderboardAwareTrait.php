<?php

namespace Eternium\Event;

trait LeaderboardAwareTrait
{
    public function withMages(string $id): self
    {
        return $this->withEvent(Leaderboard::createMages($id));
    }

    public function withWarriors(string $id): self
    {
        return $this->withEvent(Leaderboard::createWarriors($id));
    }

    public function withBountyHunters(string $id): self
    {
        return $this->withEvent(Leaderboard::createBountyHunters($id));
    }

    abstract protected function withEvent(Event $event): Event;
}
