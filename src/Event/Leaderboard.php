<?php

namespace Eternium\Event;

use Eternium\Event\Leaderboard\Entry;
use Eternium\Event\Leaderboard\Hero;
use Eternium\Event\Leaderboard\Trial;
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
        return new Leaderboard\Mage($id);
    }

    public static function createWarriors(string $id): self
    {
        return new Leaderboard\Warrior($id);
    }

    public static function createBountyHunters(string $id): self
    {
        return new Leaderboard\BountyHunter($id);
    }

    /**
     * @return Event[]
     */
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
    public function fetch(Eternium $eternium): \Generator
    {
        $rankings = $eternium->leaderboards->getRankings($this->id);
        $options = [
            'page' => 1,
            'page_size' => 1000,
            'payload' => [
                'name',
                'champion_level',
                'hero',
                'trialStats',
                'devInfo',
            ],
        ];

        $entries = 0;
        do {
            $pageEntries = 0;
            foreach ($rankings->list($options) as $data) {
                $entry = new Entry(
                    Hero::fromPayload($data['payload'] ?? null),
                    Trial::fromTrialStats($data['score'], $data['payload']['trialStats'] ?? null),
                    \strtolower($data['payload']['devInfo']['platform'] ?? ''),
                );
                ++$entries;
                ++$pageEntries;

                yield $entry;
            }
            ++$options['page'];
        } while ($pageEntries === $options['page_size']);

        return $entries;
    }
}
