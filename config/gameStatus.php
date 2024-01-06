<?php

declare(strict_types=1);

$platforms = [];

foreach ($this->api->request('GET', 'status')->toArray() as $status) {
    if ('default' !== $status['platform']) {
        $platforms[] = $status;
    }
}

return $platforms;
