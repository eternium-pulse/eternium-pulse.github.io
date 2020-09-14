<?php

namespace Eternium\Event\Leaderboard;

final class Gear
{
    public float $averageLevel;

    public function __construct(float $averageLevel)
    {
        assert(0.0 <= $averageLevel);

        $this->averageLevel = $averageLevel;
    }

    public static function fromEquipment(array $items): self
    {
        $sum = $n = 0;
        foreach ($items as $item) {
            if (!isset($item['equippedSlot'], $item['itemLevel'])) {
                continue;
            }
            extract($item);
            if ('necklace' !== $equippedSlot && 'ring' !== $equippedSlot) {
                $sum += $itemLevel;
                ++$n;
            }
        }
        if (0 === $n) {
            return new self(0.0);
        }

        return new self(round($sum / $n, 2));
    }
}
