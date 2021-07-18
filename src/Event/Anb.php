<?php

namespace Eternium\Event;

/**
 * @extends BaseEvent<League>
 */
final class Anb extends BaseEvent
{
    protected function __construct(
        public int $index,
        public ?League $bronze = null,
        public ?League $silver = null,
        public ?League $gold = null,
    ) {
        assert(0 < $index);

        $this->type = 'ANB';

        parent::__construct("anb-{$this->index}");

        if ($this->bronze) {
            $this->bronze->parent = $this;
        }
        if ($this->silver) {
            $this->silver->parent = $this;
        }
        if ($this->gold) {
            $this->gold->parent = $this;
        }
    }

    public static function create(int $index, ?League $bronze = null, ?League $silver = null, ?League $gold = null): self
    {
        return new self($index, $bronze, $silver, $gold);
    }

    /**
     * @return \Iterator<int, League>
     */
    public function getIterator(): \Iterator
    {
        if ($this->bronze) {
            yield $this->bronze;
        }
        if ($this->silver) {
            yield $this->silver;
        }
        if ($this->gold) {
            yield $this->gold;
        }
    }

    public function getName(bool $long = false): string
    {
        return "{$this->type} {$this->index}";
    }

    public function getTitle(bool $long = false): string
    {
        if ($long) {
            return "A New Beginning {$this->index}";
        }

        return "{$this->type} {$this->index}";
    }
}
