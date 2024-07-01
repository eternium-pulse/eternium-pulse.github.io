<?php

namespace Eternium\Utils;

use Wikimedia\Minify\CSSMin;
use Wikimedia\Minify\JavaScriptMinifier;

final class Minifier
{
    public function __invoke(string $type, string $code): string
    {
        return $this->tryMinify($type, $code);
    }

    /**
     * @param 'css'|'js' $type
     */
    public function minify(string $type, string $code): string
    {
        return match ($type) {
            'css' => CSSMin::minify($code),
            'js' => JavaScriptMinifier::minify($code),
            default => throw new \DomainException('Unsupported minification type'),
        };
    }

    public function tryMinify(string $type, string $code): string
    {
        try {
            return $this->minify($type, $code);
        } catch (\Throwable) {
            return $code;
        }
    }
}
