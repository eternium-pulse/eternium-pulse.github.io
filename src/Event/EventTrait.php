<?php

namespace Eternium\Event;

/**
 * @deprecated
 */
trait EventTrait
{
    public string $name;

    public EventInterface $parent;

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
