#!/usr/bin/env php
<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use EterniumPulse\Eternium;
use Symfony\Component\Console\Application;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

define('ETERNIUM_API_KEY', (string) getenv('ETERNIUM_API_KEY'));
define('ETERNIUM_DATA_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define('ETERNIUM_HTML_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR);

$events = require dirname(__DIR__).'/config/events.php';

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand(
    Eternium::createDefault(ETERNIUM_API_KEY),
    $events,
));
$application->add(new GenerateCommand(
    new Twig(new FilesystemLoader('templates', dirname(__DIR__))),
    $events,
));

exit($application->run());
