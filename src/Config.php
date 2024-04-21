<?php

namespace Eternium;

#[\AllowDynamicProperties]
class Config
{
    public function __construct(private string $path)
    {
        \assert(\is_dir($path));

        $this->path = \realpath($path);
    }

    public function __get(string $name)
    {
        \assert('' !== $name);

        $this->{$name} = null;
        $file = "{$this->path}/{$name}.php";
        if (\is_file($file)) {
            $this->{$name} = (fn (): mixed => require \func_get_arg(0))($file);
        }

        return $this->{$name};
    }
}
