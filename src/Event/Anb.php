<?php

namespace Eternium\Event;

/**
 * @extends BaseEvent<League>
 */
final class Anb extends BaseEvent
{
    public int $index;

    protected function __construct(int $index, League ...$leagues)
    {
        assert(0 < $index);

        $this->index = $index;
        $this->type = 'ANB';

        parent::__construct("anb-{$this->index}", ...$leagues);
    }

    public static function create(int $index, League ...$leagues): self
    {
        return new self($index, ...$leagues);
    }

    public function getName(bool $long = false): string
    {
        return "{$this->getType()} {$this->index}";
    }

    public function getType(bool $long = false): string
    {
        if ($long) {
            return 'A New Beginning';
        }

        return $this->type;
    }

    public function getTitle(bool $long = false): string
    {
        return "{$this->getType($long)} {$this->index}";
    }
}
