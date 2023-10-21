<?php

namespace Eternium\Event;

final class Anb extends BaseEvent
{
    public string $type = 'anb';

    protected function __construct(
        public int $index,
        public ?League $bronze = null,
        public ?League $silver = null,
        public ?League $gold = null,
        public ?League $platinum = null,
    ) {
        assert(0 < $index);

        $this->slug = "anb-{$index}";
        $this->name = "ANB {$index}";
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

    public static function create(
        int $index,
        ?League $bronze = null,
        ?League $silver = null,
        ?League $gold = null,
        ?League $platinum = null,
    ): self {
        return new self($index, $bronze, $silver, $gold, $platinum);
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
        if ($this->platinum) {
            yield $this->platinum;
        }
    }
}
