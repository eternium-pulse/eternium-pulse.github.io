<?php

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
    ])
    ->setFinder(
        (new \PhpCsFixer\Finder())
            ->files()
            ->in(['bin', 'config', 'src', 'tests'])
            ->append([basename(__FILE__)])
    )
;
