<?php

namespace Eternium\Event;

trait DateAwareTrait
{
    public \DateTimeImmutable $startDate;
    public \DateTimeImmutable $endDate;

    final public function startsOn(string | \DateTimeInterface $date): self
    {
        $this->startDate = match (is_string($date)) {
            true => new \DateTimeImmutable($date, new \DateTimeZone('UTC')),
            false => \DateTimeImmutable::createFromInterface($date),
        };

        return $this;
    }

    final public function endsOn(string | \DateTimeInterface $date): self
    {
        $this->endDate = match (is_string($date)) {
            true => new \DateTimeImmutable($date, new \DateTimeZone('UTC')),
            false => \DateTimeImmutable::createFromInterface($date),
        };

        return $this;
    }

    final public function endsIn(string | \DateInterval $duration): self
    {
        if (is_string($duration)) {
            $duration = new \DateInterval($duration);
        }

        $this->endDate = $this->startDate->add($duration);

        return $this;
    }
}
