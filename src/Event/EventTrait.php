<?php

namespace Eternium\Event;

trait EventTrait
{
    public string $name;

    public string $type;

    public EventInterface $parent;

    public Stats $stats;

    final public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->name;
    }

    final public function setParent(EventInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return array<int, EventInterface>
     */
    final public function getAncestors(): array
    {
        $parents = [];
        $e = $this;
        while (isset($e->parent)) {
            array_unshift($parents, $e->parent);
            $e = $e->parent;
        }

        return $parents;
    }

    final public function getPath(string $separator): string
    {
        return join($separator, [...$this->getAncestors(), $this]);
    }
}
