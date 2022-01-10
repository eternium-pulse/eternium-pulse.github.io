<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return new Environment(
    new FilesystemLoader([
        'templates',
        'vendor/twbs/bootstrap/dist',
    ], dirname(__DIR__)),
);
