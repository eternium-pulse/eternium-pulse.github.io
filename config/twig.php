<?php

use Eternium\Utils\Minifier;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

return (static function (): Environment {
    $twig = new Environment(
        new FilesystemLoader(['templates', 'public'], dirname(__DIR__)),
    );

    $twig->addFilter(new TwigFilter('minify_*', new Minifier(), [
        'is_safe_callback' => static fn (string $type): ?array => [$type],
    ]));

    $twig->addFunction(new TwigFunction('detect_country', function (string $name): ?string {
        if (\str_starts_with($name, 'Kor')) {
            return 'KR';
        }
        if (\str_starts_with($name, 'China')) {
            return 'CN';
        }
        if (\str_starts_with($name, 'Ru')) {
            return 'RU';
        }

        return null;
    }));

    $twig->addFunction(new TwigFunction('env', function (string $var, string $default = ''): string {
        $value = \getenv($var);
        if (false === $value) {
            $value = $default;
        }

        return $value;
    }));

    $twig->addGlobal('now', new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
    $core = $twig->getExtension(CoreExtension::class);
    $core->setTimezone('UTC');
    $core->setDateFormat('Y-m-d H:i e', '%d days');

    return $twig;
})();
