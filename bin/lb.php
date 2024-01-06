#!/usr/bin/env php
<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Eternium\Config;
use Symfony\Component\Console\Application;

\ini_set('memory_limit', '-1');

$config = new Config(dirname(__DIR__).'/config');
$config->dataPath = new \SplFileInfo(\dirname(__DIR__).'/data');
$config->htmlPath = new \SplFileInfo(\dirname(__DIR__).'/public');

$application = new Application('Eternium Pulse');
$application->add(new ConfigureCommand($config));
$application->add(new FetchCommand($config));
$application->add(new GenerateCommand($config));

exit($application->run());
