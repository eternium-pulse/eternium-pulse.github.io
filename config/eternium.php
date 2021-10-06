<?php

use EterniumPulse\Eternium;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

return new Eternium(
    new RetryableHttpClient(
        HttpClient::createForBaseUri(
            Eternium::BASE_URI,
            Eternium::getDefaultOptions(getenv('ETERNIUM_API_KEY')),
        ),
    ),
);
