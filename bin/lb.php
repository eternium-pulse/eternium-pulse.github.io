<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

$events = require dirname(__DIR__).'/config/events.php';

$twig = new Twig(new FilesystemLoader([''], dirname(__DIR__).'/templates'));

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand(...$events));
$application->add(new GenerateCommand($twig, ...$events));

exit($application->run());
