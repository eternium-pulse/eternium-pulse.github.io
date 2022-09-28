<?php

namespace Eternium\Event;

trait LeaderboardAwareTrait
{
    /**
     * @var array<string, BracketEntry>
     */
    private array $brackets;

    /**
     * @return \Iterator<int, Bracket|Leaderboard>
     */
    public function getIterator(): \Iterator
    {
        if (1 === count($this->brackets)) {
            foreach (reset($this->brackets) as $leaderboard) {
                $leaderboard->parent = $this;

                yield $leaderboard;
            }
        } else {
            foreach ($this->brackets as $name => $entry) {
                $bracket = $entry->toBracket($name);
                $bracket->parent = $this;

                yield $bracket;
            }
        }
    }

    public function hasBrackets(): bool
    {
        return 1 !== count($this->brackets);
    }

    public function withMages(string $id, string $bracket = ''): self
    {
        $this->createBracketEntry($bracket)->mages = $id;

        return $this;
    }

    public function withWarriors(string $id, string $bracket = ''): self
    {
        $this->createBracketEntry($bracket)->warriors = $id;

        return $this;
    }

    public function withBountyHunters(string $id, string $bracket = ''): self
    {
        $this->createBracketEntry($bracket)->bountyHunters = $id;

        return $this;
    }

    public function withBracket(string $bracket, string $mages = '', string $warriors = '', string $bounty_hunters = ''): self
    {
        $this->brackets[$bracket] = new BracketEntry($mages, $warriors, $bounty_hunters);

        return $this;
    }

    private function createBracketEntry(string $name): BracketEntry
    {
        return $this->brackets[$name] ??= new BracketEntry('', '', '');
    }
}

/**
 * @internal
 *
 * @implements \IteratorAggregate<int, Leaderboard>
 */
final class BracketEntry implements \IteratorAggregate
{
    public function __construct(
        public string $mages,
        public string $warriors,
        public string $bountyHunters,
    ) {
    }

    /**
     * @return \Iterator<int, Leaderboard>
     */
    public function getIterator(): \Iterator
    {
        yield Leaderboard::createMages($this->mages);

        yield Leaderboard::createWarriors($this->warriors);

        yield Leaderboard::createBountyHunters($this->bountyHunters);
    }

    public function toBracket(string $name): Bracket
    {
        return Bracket::{'create'.ucfirst($name)}($this->mages, $this->warriors, $this->bountyHunters);
    }
}
