<?php

namespace Eternium\Event;

trait DateAwareTrait
{
    public \DateTimeInterface $startDate;
    public \DateTimeInterface $endDate;

    final public function startOn(string $date): self
    {
        $this->startDate = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));

        return $this;
    }

    final public function endOn(string $date): self
    {
        $this->endDate = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));

        return $this;
    }
}
