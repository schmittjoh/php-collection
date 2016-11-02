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
 * Basic map interface.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
interface MapInterface extends \Traversable, \Countable
{
    /**
     * Returns the first element in the collection if available.
     *
     * @return Option on array<K,V>
     */
    public function headOption();

    /**
     * Returns the first element in the collection if available.
     *
     * @return null|array(K => V)
     */
    public function head();

    /**
     * Returns the last element in the collection if available.
     *
     * @return Option on array<K,V>
     */
    public function lastOption();

    /**
     * Returns the last element in the collection if available.
     *
     * @return null|array(K => V)
     */
    public function last();
    
    /**
     * Returns all elements in this collection.
     *
     * @return array
     */
    public function all();

    /**
     * @return int - number of elements in collection
     */
    public function length();

    /**
     * Searches the collection for an element.
     *
     * @param callable $callable receives the element as first argument, and returns true, or false
     *
     * @return Option on array<K,V>
     */
    public function find(callable $callable);

    /**
     * Returns the value associated with the given key.
     *
     * @param mixed $key
     *
     * @return Option on V
     */
    public function get($key);

    /**
     * Returns whether this map contains a given key.
     *
     * @param mixed $key
     *
     * @return boolean
     */
    public function containsKey($key);

    /**
     * Puts a new element in the map.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * Removes an element from the map.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function remove($key);

    /**
     * Adds all another map to this map, and returns itself.
     *
     * @param MapInterface $map
     *
     * @return MapInterface
     */
    public function addMap(MapInterface $map);

    /**
     * Returns an array with the keys.
     *
     * @return array
     */
    public function keys();

    /**
     * Returns an array with the values.
     *
     * @return array
     */
    public function values();

    /**
     * Returns a new sequence by omitting the given number of elements from the beginning.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return MapInterface
     */
    public function drop($number);

    /**
     * Returns a new sequence by omitting the given number of elements from the end.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return MapInterface
     */
    public function dropRight($number);

    /**
     * Returns a new sequence by omitting elements from the beginning for as long as the callable returns true.
     *
     * @param callable $callable Receives the element to drop as first argument, and returns true (drop), or false (stop).
     *
     * @return MapInterface
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
     * @return MapInterface
     */
    public function take($number);

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return MapInterface
     */
    public function takeWhile(callable $callable);

    /**
     * Returns a filtered collection of the same type.
     *
     * Removes all elements for which the provided callable returns false.
     *
     * @param callable $callable receives a key and an element of the map and must return true (= keep) or false (= remove).
     *
     * @return MapInterface
     */
    public function filter(callable $callable);

    /**
     * Returns a filtered collection of the same type.
     *
     * Removes all elements for which the provided callable returns false.
     *
     * @param callable $callable receives a key and an element of the map and must return true (= remove) or false (= keep).
     *
     * @return MapInterface
     */
    public function filterNot(callable $callable);

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable Callable takes function(mixed $key, mixed $value): MapInterface
     * @return MapInterface
     */
    public function map(callable $callable);

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable Callable takes function(mixed $key, mixed $value): MapInterface
     *
     * @return MapInterface
     */
    public function flatMap(callable $callable);

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
     * sliding(size: Int): Sequence[Map[A]]
     * Groups elements in fixed size blocks by passing a "sliding window" over them (as opposed to partitioning them, as is done in grouped.)
     *
     * @param int $size - the number of elements per group
     * @return SequenceInterface - An iterator producing sets of size size,
     * except the last and the only element will be truncated if there are fewer elements than size.
     */
    function sliding($size);

    /**
     * tail: Map[A, B]
     * Selects all elements except the first.
     *
     * @return MapInterface - a map consisting of all elements of this map except the first one.
     */
    function tail();
}
