<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PhpCollection;

/**
 * Basic map interface.
 *
 * @IgnoreAnnotation("template")
 * @template K the type of the keys
 * @template V the type of the values
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface MapInterface extends CollectionInterface
{
    /**
     * Returns the first element in the collection if available.
     *
     * @return Option<array<K,V>>
     */
    public function first();

    /**
     * Returns the last element in the collection if available.
     *
     * @return Option<array<K,V>>
     */
    public function last();

    /**
     * Searches the collection for an element.
     *
     * @param callable $callable receives the element as first argument, and returns true, or false
     *
     * @return Option<array<K,V>>
     */
    public function find($callable);

    /**
     * Returns the value associated with the given key.
     *
     * @param K $key
     *
     * @return Option<V>
     */
    public function get($key);

    /**
     * Returns whether this map contains a given key.
     *
     * @param K $key
     *
     * @return boolean
     */
    public function containsKey($key);

    /**
     * Puts a new element in the map.
     *
     * @param K $key
     * @param V $value
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * Removes an element from the map.
     *
     * @param K $key
     *
     * @return V
     */
    public function remove($key);

    /**
     * Adds all another map to this map, and returns itself.
     *
     * @param MapInterface<K,V> $map
     *
     * @return MapInterface<K,V>
     */
    public function addMap(MapInterface $map);

    /**
     * Returns an array with the keys.
     *
     * @return array<K>
     */
    public function keys();

    /**
     * Returns an array with the values.
     *
     * @return array<V>
     */
    public function values();

    /**
     * Returns a new sequence by omitting the given number of elements from the beginning.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return MapInterface<K,V>
     */
    public function drop($number);

    /**
     * Returns a new sequence by omitting the given number of elements from the end.
     *
     * If the passed number is greater than the available number of elements, all will be removed.
     *
     * @param integer $number
     *
     * @return MapInterface<K,V>
     */
    public function dropRight($number);

    /**
     * Returns a new sequence by omitting elements from the beginning for as long as the callable returns true.
     *
     * @param callable $callable Receives the element to drop as first argument, and returns true (drop), or false (stop).
     *
     * @return MapInterface<K,V>
     */
    public function dropWhile($callable);

    /**
     * Creates a new collection by taking the given number of elements from the beginning
     * of the current collection.
     *
     * If the passed number is greater than the available number of elements, then all elements
     * will be returned as a new collection.
     *
     * @param integer $number
     *
     * @return MapInterface<K,V>
     */
    public function take($number);

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return MapInterface<K,V>
     */
    public function takeWhile($callable);
}
