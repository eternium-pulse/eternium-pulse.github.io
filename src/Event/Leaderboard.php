<?php

namespace Eternium\Event;

use Eternium\Event\Leaderboard\BountyHunter;
use Eternium\Event\Leaderboard\Entry;
use Eternium\Event\Leaderboard\Hero;
use Eternium\Event\Leaderboard\Mage;
use Eternium\Event\Leaderboard\Trial;
use Eternium\Event\Leaderboard\Warrior;
use Eternium\Utils;
use EterniumPulse\Eternium;

abstract class Leaderboard extends Event
{
    public string $type = 'leaderboard';

    private function __construct(
        public string $id,
    ) {
        assert(24 === strlen($id) && ctype_xdigit($id));
    }

    public static function createMages(string $id): self
    {
        return new Mage($id);
    }

    public static function createWarriors(string $id): self
    {
        return new Warrior($id);
    }

    public static function createBountyHunters(string $id): self
    {
        return new BountyHunter($id);
    }

    /**
     * @return Event[]
     */
    #[\Override]
    public function getPath(): array
    {
        return [...$this->parent->getPath(), $this];
    }

    /**
     * @return \Generator<int, Entry, void, int>
     */
    public function read(\SplFileInfo $file): \Generator
    {
        $header = [];
        $reader = Utils::createCsvReader($file, $header);
        foreach ($reader as $data) {
            $entry = Entry::fromArray($data);

            yield $entry;
        }

        return $reader->getReturn();
    }

    /**
     * @return \Generator<void, void, ?Entry, int>
     */
    public function write(\SplFileInfo $file): \Generator
    {
        $writer = Utils::createCsvWriter($file, Entry::HEADER);
        while (null !== ($entry = yield)) {
            if ($entry instanceof Entry) {
                $writer->send($entry->toArray());
            }
        }
        $writer->send($entry);

        return $writer->getReturn();
    }

    /**
     * @return \Generator<int, Entry, void, int>
     */
    public function fetch(Eternium $eternium, int $pageSize = 1000): \Generator
    {
        \assert($pageSize > 0);

        $rankings = $eternium->leaderboards->rankings($this->id);
        $page = 1;
        $entries = 0;
        do {
            $pageEntries = 0;
            foreach ($rankings->list($page, $pageSize) as $data) {
                $entry = new Entry(
                    Hero::fromPayload($data['payload'] ?? null),
                    Trial::fromTrialStats($data['score'], $data['payload']['trialStats'] ?? null),
                    \strtolower($data['payload']['devInfo']['platform'] ?? ''),
                );
                ++$entries;
                ++$pageEntries;

                yield $entry;
            }
            ++$page;
        } while ($pageEntries === $pageSize);

        return $entries;
    }
}
