<?php

namespace Eternium\Event;

trait LeaderboardAwareTrait
{
    public Leaderboard $mages;

    public Leaderboard $warriors;

    public Leaderboard $bountyHunters;

    /**
     * @return \Iterator<int, Leaderboard>
     */
    public function getIterator(): \Iterator
    {
        assert(isset($this->mages, $this->warriors, $this->bountyHunters));

        yield $this->mages;
        yield $this->warriors;
        yield $this->bountyHunters;
    }

    public function withMages(string $id): self
    {
        $this->mages = Leaderboard::createMages($id);
        $this->mages->parent = $this;

        return $this;
    }

    public function withWarriors(string $id): self
    {
        $this->warriors = Leaderboard::createWarriors($id);
        $this->warriors->parent = $this;

        return $this;
    }

    public function withBountyHunters(string $id): self
    {
        $this->bountyHunters = Leaderboard::createBountyHunters($id);
        $this->bountyHunters->parent = $this;

        return $this;
    }

    /**
     * @deprecated
     */
    abstract protected function withEvent(Event $event): Event;
}
