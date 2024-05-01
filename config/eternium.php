<?php

declare(strict_types=1);

use EterniumPulse\Eternium;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

return new Eternium(
    new RetryableHttpClient(
        HttpClient::createForBaseUri(
            Eternium::BASE_URI,
            Eternium::getDefaultOptions(
                $_SERVER['ETERNIUM_API_KEY']
                    ?? throw new LogicException('Eternium API key not set'),
            ),
        ),
    ),
);
