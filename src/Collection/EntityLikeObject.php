<?php

namespace Collection;

/**
 * Implementation for ObjectBasics for entity-like objects.
 *
 * Two objects are considered equal if they refer to the same instance.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
trait EntityLikeObject
{
    public function hash()
    {
        return spl_object_hash($this);
    }

    public function equals(ObjectBasics $other)
    {
        return $this === $other;
    }
}