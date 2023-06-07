<?php

declare(strict_types=1);

namespace Eternium\Event\Leaderboard;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Trial::class)]
final class TrialTest extends TestCase
{
    #[TestWith([572, 315, 315, 257])]
    #[TestWith([572, 422, 303, 269])]
    #[TestWith([541, 540, 470, 71])]
    #[TestWith([463, 378, 0, 85])]
    #[TestWith([293, 360, 293, 0])]
    public function testDetectBossTime(int $trialTime, int $trashEndTime, int $bossStartTime, int $expected): void
    {
        $this->assertSame($expected, Trial::detectBossTime($trialTime, $trashEndTime, $bossStartTime));
    }
}
