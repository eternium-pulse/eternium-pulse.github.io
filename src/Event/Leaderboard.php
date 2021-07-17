<?php

namespace Eternium\Event;

use Eternium\Event\Leaderboard\Champion;
use Eternium\Event\Leaderboard\Entry;
use Eternium\Event\Leaderboard\Gear;
use Eternium\Event\Leaderboard\Hero;
use Eternium\Event\Leaderboard\Trial;
use Eternium\Utils;
use EterniumPulse\Eternium;

final class Leaderboard extends Event implements EventInterface
{
    use EventTrait;

    public string $id;

    private function __construct(string $slug, string $name, string $id)
    {
        assert(24 === strlen($id) && ctype_xdigit($id));

        $this->slug = $slug;
        $this->name = $name;
        $this->type = 'Leaderboard';
        $this->id = $id;
    }

    public static function createMages(string $id): self
    {
        return new self('mage', 'Mages', $id);
    }

    public static function createWarriors(string $id): self
    {
        return new self('warrior', 'Warriors', $id);
    }

    public static function createBountyHunters(string $id): self
    {
        return new self('bounty-hunter', 'Bounty Hunters', $id);
    }

    public function getName(bool $long = false): string
    {
        return $this->name;
    }

    public function getType(bool $long = false): string
    {
        return 'Leaderboard';
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->name} {$this->type}";
    }

    public function walk(\Generator $handler): void
    {
        $handler->send($this);
    }

    /**
     * @return \Generator<int, Entry, void, int>
     */
    public function read(string $file): \Generator
    {
        $reader = Utils::createCsvReader($file);
        foreach ($reader as $data) {
            $entry = Entry::fromArray($data);
            yield $entry;
        }

        return $reader->getReturn();
    }

    /**
     * @return \Generator<void, void, ?Entry, int>
     */
    public function write(string $file): \Generator
    {
        $writer = Utils::createCsvWriter($file);
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
                'hero.selectedPlayerNameID',
                'hero.equipped.itemLevel',
                'trialStats.heroDeaths',
            ],
        ];

        $entries = 0;
        do {
            $pageEntries = 0;
            foreach ($rankings->list($options) as $data) {
                $entry = new Entry(
                    new Hero(
                        $data['payload']['name'],
                        ucwords(strtr($data['payload']['hero']['selectedPlayerNameID'] ?? '', '_', ' ')),
                    ),
                    new Champion($data['payload']['champion_level']),
                    Gear::fromEquipment($data['payload']['hero']['equipped'] ?? []),
                    Trial::fromScore($data['score'], $data['payload']['trialStats']['heroDeaths']),
                );
                $this->stats->add(1, $entry->champion->level, $entry->trial->level);
                ++$entries;
                ++$pageEntries;
                yield $entry;
            }
            ++$options['page'];
        } while ($pageEntries === $options['page_size']);

        return $entries;
    }
}
