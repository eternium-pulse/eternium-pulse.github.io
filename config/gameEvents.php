<?php

declare(strict_types=1);

$events = [];

foreach ($this->api->request('GET', 'events')->toArray() as $event) {
    $event['start_date'] /= 1000;
    $event['end_date'] /= 1000;
    if ($event['end_date'] > $_SERVER['REQUEST_TIME']) {
        $events[] = $event;
    }
}

return $events;
