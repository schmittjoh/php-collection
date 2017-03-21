<?php
namespace Collection;

use PhpOption\Option;

/**
 * Interface for sets.
 *
 * Each Set contains equal values only once.
 *
 * @author Artyom Sukharev , J. M. Schmitt
 */
interface SetInterface extends CollectionInterface, \IteratorAggregate
{
    /**
     * @param object|scalar $elem
     * @return SetInterface
     */
    public function add($elem);

    /**
     * @param \Traversable|array $elements
     * @return SetInterface
     */
    public function addAll($elements);

    /**
     * @param object|scalar $elem
     * @return SetInterface
     */
    public function remove($elem);

    /**
     * Returns the first element in the collection if available.
     *
     * @return Option
     */
    public function headOption();

    /**
     * Returns the first element in the collection if available.
     *
     * @return null|mixed
     */
    public function head();

    /**
     * Returns the last element in the collection if available.
     *
     * @return Option
     */
    public function lastOption();

    /**
     * Returns the last element in the collection if available.
     *
     * @return null|mixed
     */
    public function tail();

    /**
     * Returns all elements in this Set.
     *
     * @return array
     */
    public function all();

    /**
     * @return int - number of elements in collection
     */
    function length();

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
     * @return SetInterface
     */
    public function take($number);

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function takeWhile(callable $callable);

    /**
     * Builds a new set by applying a function to all elements of this immutable set
     * and using the elements of the resulting collections.
     *
     * @param callable $callable
     * @return SetInterface
     */
    public function flatMap(callable $callable);

    /**
     * Builds a new collection by applying a function to all elements of this immutable set.
     *
     * @param callable $callable
     * @return SetInterface
     */
    public function map(callable $callable);

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (= keep) or false (= remove).
     *
     * @return SequenceInterface
     */
    function filter(callable $callable);

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (= remove) or false (= keep).
     *
     * @return SetInterface
     */
    function filterNot(callable $callable);

    /**
     * Applies a binary operator to a start value and all elements of this set, going left to right.
     * foldLeft[B](z: B, op: (B, A) ⇒ B): B
     *
     * B - the result type of the binary operator.
     * z - the start value.
     * op -the binary operator.
     *
     * @param mixed $initialValue - the start value
     * @param callable $callable - the binary operator
     * @return mixed - the result of inserting op between consecutive elements of this set, going left to right with the start value z on the left:
     */
    function foldLeft($initialValue, callable $callable);

    /**
     * Applies a binary operator to all elements of this set and a start value, going right to left.
     * foldRight[B](z: B, op: (A, B) ⇒ B): B
     *
     * B - the result type of the binary operator.
     * z - the start value.
     * op -the binary operator.
     *
     * @param mixed $initialValue - the start value
     * @param callable $callable - the binary operator
     * @return mixed - the result of inserting op between consecutive elements of this set, going left to right with the start value z on the left:
     */
    function foldRight($initialValue, callable $callable);

    /**
     * sliding(size: Int): Sequence[Set[A]]
     * Groups elements in fixed size blocks by passing a "sliding window" over them (as opposed to partitioning them, as is done in grouped.)
     *
     * @param int $size - the number of elements per group
     * @return SequenceInterface - An iterator producing sets of size size,
     * except the last and the only element will be truncated if there are fewer elements than size.
     */
    function sliding($size);
}
