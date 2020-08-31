<?php

namespace Eternium\Command;

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$events = require dirname(__DIR__).'/config/events.php';

$application = new Application('Eternium Pulse');
$application->add(new FetchCommand(...$events));

exit($application->run());
