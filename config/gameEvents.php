<?php

declare(strict_types=1);

$now = $_SERVER['REQUEST_TIME'] * 1000;

return \array_filter(
    $this->api->request('GET', 'events')->toArray(),
    static fn (array $event): bool => $event['end_date'] > $now,
);
