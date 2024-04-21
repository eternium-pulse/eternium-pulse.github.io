<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@PhpCsFixer' => true,
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setFinder(
        (new Finder())
            ->files()
            ->in(['bin', 'config', 'src', 'tests'])
            ->append([basename(__FILE__), 'rector.php'])
    )
;
