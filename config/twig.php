<?php

use Eternium\Utils\Minifier;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

return (static function (): Environment {
    $twig = new Environment(
        new FilesystemLoader(['templates', 'public'], dirname(__DIR__)),
    );

    $twig->addFilter(new TwigFilter('minify_*', new Minifier(), [
        'is_safe_callback' => static fn (string $type): ?array => [$type],
    ]));

    $twig->addGlobal('now', new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
    $core = $twig->getExtension(CoreExtension::class);
    $core->setTimezone('UTC');
    $core->setDateFormat(\DateTime::W3C, '%d days');

    return $twig;
})();
