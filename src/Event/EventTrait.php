<?php

namespace Eternium\Event;

/**
 * @deprecated
 */
trait EventTrait
{
    public string $name;

    public Event $parent;

    final public function setParent(Event $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return array<int, Event>
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
}
