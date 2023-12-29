<?php

declare(strict_types=1);

use Symfony\Component\HttpClient\HttpClient;

return HttpClient::createForBaseUri('https://eternium.pages.dev/api/v1/');
