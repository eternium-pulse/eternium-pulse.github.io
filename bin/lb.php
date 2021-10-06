#!/usr/bin/env php
<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Console\Application;

define('ETERNIUM_CONFIG_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR);
define('ETERNIUM_DATA_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define('ETERNIUM_HTML_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR);

$events = require ETERNIUM_CONFIG_PATH.'events.php';

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand(
    require ETERNIUM_CONFIG_PATH.'eternium.php',
    $events,
));
$application->add(new GenerateCommand(
    require ETERNIUM_CONFIG_PATH.'twig.php',
    $events,
));

exit($application->run());
