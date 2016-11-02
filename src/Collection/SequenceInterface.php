<?php
/*
 * Copyright (C) 2016 Johannes M. Schmitt, Artyom Sukharev <aly.casus@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation, version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */
namespace Collection;

use PhpOption\Option;

/**
 * Interface for mutable sequences.
 *
 * Equality of elements in the sequence is established via a shallow comparison (===).
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
interface SequenceInterface extends CollectionInterface
{
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
    public function last();

    /**
     * Returns all elements in this sequence.
     *
     * @return array
     */
    public function all();

    /**
     * Returns a new Sequence with all elements in reverse order.
     *
     * @return SequenceInterface
     */
    public function reverse();

    /**
     * Adds the elements of another sequence to this sequence.
     *
     * @param SequenceInterface $seq
     *
     * @return SequenceInterface
     */
    public function addSequence(SequenceInterface $seq);

    /**
     * Returns the index of the passed element.
     *
     * @param mixed $elem
     *
     * @return integer the index (0-based), or -1 if not found
     */
    public function indexOf($elem);

    /**
     * Returns the last index of the passed element.
     *
     * @param mixed $elem
     * @return integer the index (0-based), or -1 if not found
     */
    public function lastIndexOf($elem);

    /**
     * Returns whether the given index is defined in the sequence.
     *
     * @param integer $index (0-based)
     * @return boolean
     */
    public function isDefinedAt($index);

    /**
     * Returns the first index where the given callable returns true.
     *
     * @param callable $callable receives the element as first argument, and returns true, or false
     *
     * @return integer the index (0-based), or -1 if the callable returns false for all elements
     */
    public function indexWhere(callable $callable);

    /**
     * Returns the last index where the given callable returns true.
     *
     * @param callable $callable receives the element as first argument, and returns true, or false
     *
     * @return integer the index (0-based), or -1 if the callable returns false for all elements
     */
    public function lastIndexWhere(callable $callable);

    /**
     * Returns all indices of this collection.
     *
     * @return integer[]
     */
    public function indices();

    /**
     * Returns the element at the given index.
     *
     * @param integer $index (0-based)
     *
     * @return mixed
     */
    public function get($index);

    /**
     * Adds an element to the sequence.
     *
     * @param mixed $elem
     *
     * @return void
     */
    public function add($elem);

    /**
     * Removes the element at the given index, and returns it.
     *
     * @param integer $index
     *
     * @return mixed
     */
    public function remove($index);

    /**
     * Adds all elements to the sequence.
     *
     * @param array|\Traversable $elements
     *
     * @return void
     */
    public function addAll($elements);

    /**
     * Updates the value at the given index.
     *
     * @param integer $index
     * @param mixed $value
     *
     * @return void
     */
    public function update($index, $value);

    /**
     * Returns a new sequence by omitting the given number of elements from the beginning.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return SequenceInterface
     */
    public function drop($number);

    /**
     * Returns a new sequence by omitting the given number of elements from the end.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return SequenceInterface
     */
    public function dropRight($number);

    /**
     * Returns a new sequence by omitting elements from the beginning for as long as the callable returns true.
     *
     * @param callable $callable Receives the element to drop as first argument, and returns true (drop), or false (stop).
     *
     * @return SequenceInterface
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
     * @return SequenceInterface
     */
    public function take($number);

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return SequenceInterface
     */
    public function takeWhile(callable $callable);

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable
     * @return SequenceInterface
     */
    public function map(callable $callable);

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable  Callable takes (x : \Traversable) => \Traversable
     * @return SequenceInterface
     */
    public function flatMap(callable $callable);

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
     * @return SequenceInterface
     */
    function filterNot(callable $callable);

    /**
     * Applies a binary operator to a start value and all elements of this sequence, going left to right.
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
     * Applies a binary operator to all elements of this sequence and a start value, going right to left.
     * foldRight[B](z: B)(op: (A, B) ⇒ B): B
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
     * Returns a collection when any first level nesting is flattened into the single
     * returned collection
     *
     * @return SequenceInterface
     */
    public function flatten();

    /**
     * sliding(size: Int): Sequence[Sequence[A]]
     * Groups elements in fixed size blocks by passing a "sliding window" over them (as opposed to partitioning them, as is done in grouped.)
     *
     * @param int $size - the number of elements per group
     * @return SequenceInterface - An iterator producing sets of size size,
     * except the last and the only element will be truncated if there are fewer elements than size.
     */
    function sliding($size);

    /**
     * tail: Sequence[A]
     * Selects all elements except the first.
     *
     * @return SequenceInterface - a sequence consisting of all elements of this sequence except the first one.
     */
    function tail();
}
