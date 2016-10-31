<?php

namespace Collection;
use PhpOption\Option;

/**
 * Interface for sets.
 *
 * Each Set contains equal values only once.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
interface SetInterface extends CollectionInterface, \IteratorAggregate
{
    /**
     * @param object|scalar $elem
     * @return void
     */
    public function add($elem);

    /**
     * @param \Traversable|array $elements
     * @return void
     */
    public function addAll($elements);

    /**
     * @param object|scalar $elem
     * @return void
     */
    public function remove($elem);

    /**
     * Returns the first element in the collection if available.
     *
     * @return Option
     */
    public function first();

    /**
     * Returns the last element in the collection if available.
     *
     * @return Option
     */
    public function last();

    /**
     * Returns all elements in this Set.
     *
     * @return array
     */
    public function all();

    /**
     * Returns a new Set with all elements in reverse order.
     *
     * @return SetInterface
     */
    public function reverse();

    /**
     * Adds the elements of another Set to this Set.
     *
     * @param SetInterface $seq
     *
     * @return SetInterface
     */
    public function addSet(SetInterface $seq);

    /**
     * Returns a new Set by omitting the given number of elements from the beginning.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return SetInterface
     */
    public function drop($number);

    /**
     * Returns a new Set by omitting the given number of elements from the end.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return SetInterface
     */
    public function dropRight($number);

    /**
     * Returns a new Set by omitting elements from the beginning for as long as the callable returns true.
     *
     * @param callable $callable Receives the element to drop as first argument, and returns true (drop), or false (stop).
     *
     * @return SetInterface
     */
    public function dropWhile(callable $callable);

    /**
     * Creates a new collection by taking the given number of elements from the beginning
     * of the current collection.
     *
     * If the passed number is greater than the available number of elements, then all elements
     * will be returned as a new collection.
     *
     * @param integer $number
     *
     * @return CollectionInterface
     */
    public function take($number);

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return CollectionInterface
     */
    public function takeWhile(callable $callable);

    /**
     * Builds a new collection by applying a function to all elements of this immutable set.
     *
     * @param callable $callable
     * @return CollectionInterface
     */
    public function map(callable $callable);

    /**
     * Builds a new collection by applying a function to all elements of this immutable set
     * and using the elements of the resulting collections.
     *
     * @param callable $callable
     * @return CollectionInterface
     */
    public function flatMap(callable $callable);
}