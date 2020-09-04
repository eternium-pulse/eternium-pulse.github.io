#!/usr/bin/env php
<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

define('ETERNIUM_API_KEY', (string) getenv('ETERNIUM_API_KEY'));
define('ETERNIUM_DATA_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define('ETERNIUM_HTML_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR);

$events = require dirname(__DIR__).'/config/events.php';

$twig = new Twig(new FilesystemLoader([''], dirname(__DIR__).'/templates'));

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand(...$events));
$application->add(new GenerateCommand($twig, ...$events));

exit($application->run());
