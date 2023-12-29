<?php

declare(strict_types=1);

return \array_slice($this->api->request('GET', 'status')->toArray(), 1);
