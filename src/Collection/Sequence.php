<?php
namespace Collection;

/**
 * Unsorted sequence implementation.
 *
 * Characteristics:
 *
 *     - Keys: consequentially numbered, without gaps
 *     - Values: anything, duplicates allowed
 *     - Ordering: same as input unless when explicitly sorted
 *
 * @author Artyom Sukharev , J. M. Schmitt
 */
class Sequence extends AbstractSequence implements SortableInterface, \JsonSerializable
{
    /**
     * @param $callable
     *
     * @return SequenceInterface
     */
    public function sortWith(callable $callable)
    {
        usort($this->elements, $callable);

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->all();
    }
}
