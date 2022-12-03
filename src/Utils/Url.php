<?php

declare(strict_types=1);

namespace Eternium\Utils;

final class Url implements \Stringable
{
    public function __construct(
        public string $scheme = '',
        public string $authority = '',
        public string $path = '',
        public ?string $query = null,
        public ?string $fragment = null,
    ) {
    }

    public function __toString(): string
    {
        $url = $this->path;
        if ('' !== $this->authority) {
            $url = \ltrim($url, '/');
            if ('' !== $url) {
                $url = "/{$url}";
            }
            $url = "//{$this->authority}{$url}";
            if ('' !== $this->scheme) {
                $url = "{$this->scheme}:{$url}";
            }
        }
        if (null !== $this->query) {
            $url .= "?{$this->query}";
        }
        if (null !== $this->fragment) {
            $url .= "#{$this->fragment}";
        }

        return $url;
    }

    public static function parse(string $url): ?self
    {
        $parts = \parse_url($url);
        if (false === $parts) {
            return null;
        }

        $authority = $parts['host'] ?? '';
        if ('' !== $authority) {
            if (isset($parts['port'])) {
                $authority .= ":{$parts['port']}";
            }
            $user = $parts['user'] ?? '';
            if ('' !== $user) {
                $authority = "@{$authority}";
                $pass = $parts['pass'] ?? '';
                if ('' !== $pass) {
                    $authority = ":{$authority}";
                }
                $authority = "{$user}{$authority}";
            }
        }

        return new self(
            scheme: $parts['scheme'] ?? '',
            authority: $authority,
            path: $parts['path'] ?? '',
            query: $parts['query'] ?? null,
            fragment: $parts['fragment'] ?? null,
        );
    }

    public function resolve(string $path): self
    {
        $url = new self(
            scheme: $this->scheme,
            authority: $this->authority,
        );

        if (\str_starts_with($path, '/')) {
            $url->path = $path;
        } elseif ('' === $this->path) {
            $url->path = "/{$path}";
        } elseif (\str_ends_with($this->path, '/')) {
            $url->path = "{$this->path}{$path}";
        } else {
            $url->path = \substr($this->path, 0, \strrpos($this->path, '/') + 1).$path;
        }

        return $url;
    }
}
