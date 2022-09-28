<?php

declare(strict_types=1);

namespace Eternium\Event\Leaderboard;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Eternium\Event\Leaderboard\Trial
 */
final class TrialTest extends TestCase
{
    /**
     * @testWith [572, 315, 315, 257]
     *           [572, 422, 303, 269]
     *           [541, 540, 470, 71]
     *           [463, 378, 0, 85]
     *           [293, 360, 293, 0]
     */
    public function testDetectBossTime(int $trialTime, int $trashEndTime, int $bossStartTime, int $expected): void
    {
        $this->assertSame($expected, Trial::detectBossTime($trialTime, $trashEndTime, $bossStartTime));
    }
}
