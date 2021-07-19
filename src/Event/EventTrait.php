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
}
