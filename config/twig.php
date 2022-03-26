<?php

use Eternium\Utils\Minifier;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

return (static function (): Environment {
    $twig = new Environment(
        new FilesystemLoader(['templates', 'public'], dirname(__DIR__)),
    );
    $twig->addFilter(new TwigFilter('minify_*', new Minifier(), [
        'is_safe_callback' => static fn (string $type): ?array => [$type],
    ]));

    return $twig;
})();
