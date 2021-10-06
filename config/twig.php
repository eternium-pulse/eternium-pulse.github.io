<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return new Environment(
    new FilesystemLoader('templates', dirname(__DIR__)),
);
