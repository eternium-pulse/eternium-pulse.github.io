#!/usr/bin/env php
<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Eternium\Config;
use Symfony\Component\Console\Application;

ini_set('memory_limit', '-1');

$config = new Config(dirname(__DIR__).'/config');

define('ETERNIUM_DATA_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define('ETERNIUM_HTML_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR);

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand($config->eternium, $config->events));
$application->add(new GenerateCommand($config->twig, $config->events));

exit($application->run());
