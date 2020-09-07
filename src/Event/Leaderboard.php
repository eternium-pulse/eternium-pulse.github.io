<?php

namespace Eternium\Event;

use Eternium\Utils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Leaderboard implements EventInterface
{
    private string $name;

    private string $id;

    private function __construct(string $name, string $id)
    {
        assert(24 === strlen($id) && ctype_xdigit($id));

        $this->name = $name;
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->toString();
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

    /**
     * @return array{int, int}
     */
    public static function parseScore(int $score): array
    {
        return [(int) ($score / 10000), 9999 - $score % 10000];
    }

    /**
     * @return array{name: string, title: string, clevel: int, score: int, tlevel: int, time: int, deaths: int}
     */
    public static function createEntry(string $name, string $title, int $clevel, int $score, int $deaths): array
    {
        $entry['name'] = $name;
        $entry['title'] = $title;
        $entry['clevel'] = $clevel;
        $entry['score'] = $score;
        [$entry['tlevel'], $entry['time']] = self::parseScore($score);
        $entry['deaths'] = $deaths;

        return $entry;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return 'leaderboard';
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function walk(\Generator $handler, EventInterface ...$chain): void
    {
        $handler->send([$this, ...$chain]);
    }

    public function read(string $file): \Generator
    {
        $reader = Utils::createCsvReader($file);
        foreach ($reader as $data) {
            yield self::createEntry(...$data);
        }

        return $reader->getReturn();
    }

    public function write(string $file): \Generator
    {
        $writer = Utils::createCsvWriter($file);
        while (is_array($entry = yield)) {
            $writer->send([
                $entry['name'],
                $entry['title'],
                $entry['clevel'],
                $entry['score'],
                $entry['deaths'],
            ]);
        }
        $writer->send($entry);

        return $writer->getReturn();
    }

    public function fetch(HttpClientInterface $client = null): \Generator
    {
        $client = $client ?? Utils::createHttpClient();
        $uri = "leaderboards/{$this->id}/rankings";
        $query = [
            'page' => 1,
            'pageSize' => 1000,
            'payload' => 'name,champion_level,hero.selectedPlayerNameID,trialStats.heroDeaths',
        ];

        $entries = 0;
        do {
            $pageEntries = 0;
            $response = $client->request('GET', $uri, ['query' => $query]);
            foreach ($response->toArray() as $data) {
                yield self::createEntry(
                    $data['payload']['name'],
                    ucwords(strtr($data['payload']['hero']['selectedPlayerNameID'] ?? '', '_', ' ')),
                    $data['payload']['champion_level'],
                    $data['score'],
                    $data['payload']['trialStats']['heroDeaths'],
                );
                ++$entries;
                ++$pageEntries;
            }
            ++$query['page'];
        } while ($pageEntries === $query['pageSize']);

        return $entries;
    }
}
