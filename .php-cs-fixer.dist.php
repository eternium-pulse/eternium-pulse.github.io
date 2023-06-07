<?php

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setFinder(
        (new \PhpCsFixer\Finder())
            ->files()
            ->in(['bin', 'config', 'src', 'tests'])
            ->append([basename(__FILE__)])
    )
;
