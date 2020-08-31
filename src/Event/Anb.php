<?php

namespace Eternium\Event;

final class Anb extends BaseEvent
{
    public static function n(int $n, League ...$leagues): self
    {
        \assert(0 < $n);

        return new self("anb.{$n}", ...$leagues);
    }
}
