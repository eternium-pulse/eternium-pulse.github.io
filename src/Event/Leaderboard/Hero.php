<?php

namespace Eternium\Event\Leaderboard;

final class Hero implements \Stringable
{
    public function __construct(
        public string $name,
        public string $title = '',
        public int $championLevel = 0,
        public float $averageItemLevel = 0.0,
    ) {
        assert('' !== $name);
        assert($championLevel >= 0);
        assert($averageItemLevel >= 0.0);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function fromPayload(?array $payload): self
    {
        if (null === $payload) {
            return new self('-');
        }

        assert(isset($payload['name']));
        assert(isset($payload['champion_level']));

        return new self(
            $payload['name'],
            ucwords(strtr($payload['hero']['selectedPlayerNameID'] ?? '', '_', ' ')),
            $payload['champion_level'],
            self::getAverageLevel(...array_column($payload['hero']['equipped'] ?? [], 'itemLevel')),
        );
    }

    public static function getAverageLevel(int ...$levels): float
    {
        return array_sum($levels) / (count($levels) ?: 1);
    }
}
