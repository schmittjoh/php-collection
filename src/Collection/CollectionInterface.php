<?php
namespace Collection;

/**
 * Basic interface which adds some behaviors, and a few methods common to all collections.
 *
 * @author Artyom Sukharev, J. M. Schmitt
 * @deprecated Rely only on direct interfaces e.g. MapInterface, SequenceInterface e.t.c.
 */
interface CollectionInterface extends \Traversable, \Countable
{
    /**
     * Returns whether this collection contains the passed element.
     *
     * @param mixed $elem
     *
     * @return boolean
     */
    public function contains($elem);

    /**
     * Returns whether the collection is empty.
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Returns a filtered collection of the same type.
     *
     * Removes all elements for which the provided callable returns false.
     *
     * @param callable $callable receives an element of the collection and must return true (= keep) or false (= remove).
     *
     * @return CollectionInterface
     */
    public function filter(callable $callable);

    /**
     * Returns a filtered collection of the same type.
     *
     * Removes all elements for which the provided callable returns true.
     *
     * @param callable $callable receives an element of the collection and must return true (= remove) or false (= keep).
     *
     * @return CollectionInterface
     */
    public function filterNot(callable $callable);

    /**
     * Applies the callable to an initial value and each element, going left to right.
     *
     * @param mixed $initialValue
     * @param callable $callable receives the current value (the first time this equals $initialValue) and the element
     *
     * @return mixed the last value returned by $callable, or $initialValue if collection is empty.
     */
    public function foldLeft($initialValue, callable $callable);

    /**
     * Applies the callable to each element, and an initial value, going right to left.
     *
     * @param mixed $initialValue
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     * @return mixed the last value returned by $callable, or $initialValue if collection is empty.
     */
    public function foldRight($initialValue, callable $callable);

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     * @return CollectionInterface
     */
    public function map(callable $callable);

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable Callable takes (x : \Traversable) => \Traversable
     *
     * @return CollectionInterface
     */
    public function flatMap(callable $callable);
}
