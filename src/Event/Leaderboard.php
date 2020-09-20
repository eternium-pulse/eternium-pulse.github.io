<?php

namespace Eternium\Event;

use Eternium\Event\Leaderboard\Champion;
use Eternium\Event\Leaderboard\Entry;
use Eternium\Event\Leaderboard\Gear;
use Eternium\Event\Leaderboard\Hero;
use Eternium\Event\Leaderboard\Trial;
use Eternium\Utils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Leaderboard implements EventInterface
{
    use EventTrait;

    public string $id;

    private function __construct(string $name, string $id)
    {
        assert(24 === strlen($id) && ctype_xdigit($id));

        $this->name = $name;
        $this->type = 'leaderboard';
        $this->stats = new Stats();
        $this->id = $id;
    }

    public static function mage(string $id): self
    {
        return new self('mage', $id);
    }

    public static function warrior(string $id): self
    {
        return new self('warrior', $id);
    }

    public static function bountyHunter(string $id): self
    {
        return new self('bounty-hunter', $id);
    }

    public function getName(bool $long = false): string
    {
        return ucwords(strtr("{$this->name}s", '-', ' '));
    }

    public function getType(bool $long = false): string
    {
        return ucfirst($this->type);
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->getName($long)} {$this->getType($long)}";
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
            $this->stats->add(1, $entry->champion->level, $entry->trial->level);
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
    public function fetch(HttpClientInterface $client = null): \Generator
    {
        $client = $client ?? Utils::createHttpClient();
        $uri = "leaderboards/{$this->id}/rankings";
        $query = [
            'page' => 1,
            'pageSize' => 1000,
            'payload' => join(',', [
                'name',
                'champion_level',
                'cheater',
                'hero.selectedPlayerNameID',
                'hero.equipped.itemLevel',
                'hero.equipped.equippedSlot',
                'trialStats.heroDeaths',
            ]),
        ];

        $entries = 0;
        do {
            $pageEntries = 0;
            $response = $client->request('GET', $uri, ['query' => $query]);
            foreach ($response->toArray() as $data) {
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
            ++$query['page'];
        } while ($pageEntries === $query['pageSize']);

        return $entries;
    }
}
