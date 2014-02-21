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

use PhpOption\Some;
use PhpOption\None;

/**
 * A simple map implementation which basically wraps an array with an object oriented interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AbstractMap extends AbstractCollection implements \IteratorAggregate, MapInterface, \ArrayAccess
{
    protected $elements;

    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    /**
     * Sets an entry in the map
     * 
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        // To prevent adding a NULL key, the construction $map[] will 
        // execute with $key == null and a map doest not support that.
        if ($key == NULL) { 
            throw new \InvalidArgumentException("Map cannot be used with an empty key");
        }
        $this->elements[$key] = $value;
    }

    /**
     * Executes a function to find out if an element exists in the map
     * The function will be executed with $key, $value as arguments and 
     * should return a boolean. The function will return on the first 
     * 
     * For example:
     * 
     * function ($key, $value) { 
     *    return ($value->getId() == 5);
     * }
     *  
     * @param function $callable
     * @return boolean
     */
    public function exists($callable)
    {
        foreach ($this as $k => $v) {
            if ($callable($k, $v) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets all key/value pairs in the map.
     *
     * @param array $kvMap
     *
     * @return void
     */
    public function setAll(array $kvMap)
    {
        $this->elements = array_merge($this->elements, $kvMap);
    }

    public function addMap(MapInterface $map)
    {
        foreach ($map as $k => $v) {
            $this->elements[$k] = $v;
        }
    }

    /**
     * Get an element from the map by key
     * 
     * @param mixed $key
     * @return \PhpOption\Some
     */
    public function get($key)
    {
        if (isset($this->elements[$key])) {
            return new Some($this->elements[$key]);
        }

        return None::create();
    }

    /**
     * Removes an entry by key, returns the element removed or an exception 
     * when the element does not exist.
     * 
     * @param mixed $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function remove($key)
    {
        if ( ! isset($this->elements[$key])) {
            throw new \InvalidArgumentException(sprintf('The map has no key named "%s".', $key));
        }

        $element = $this->elements[$key];
        unset($this->elements[$key]);

        return $element;
    }

    /**
     *  Empties the map
     * 
     */
    public function clear()
    {
        $this->elements = array();
    }

    /**
     * Get the first element of the map
     * 
     * @return \PhpOption\Some
     */
    public function first()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = reset($this->elements);

        return new Some(array(key($this->elements), $elem));
    }

    /**
     * Get the last element of the map
     * 
     * @return \PhpOption\Some
     */
    public function last()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = end($this->elements);

        return new Some(array(key($this->elements), $elem));
    }

    /**
     * Checks if an element exists at least once
     * 
     * @param mixed $elem
     * @return boolean
     */
    public function contains($elem)
    {
        foreach ($this->elements as $existingElem) {
            if ($existingElem === $elem) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is a key exists
     * 
     * @param mixed $key
     * @return boolean
     */
    public function containsKey($key)
    {
        return isset($this->elements[$key]);
    }

    /**
     * 
     * @return type
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }
    
    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= keep), or false (= remove).
     *
     * @return AbstractMap
     */
    public function filter($callable)
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= remove), or false (= keep).
     *
     * @return AbstractMap
     */
    public function filterNot($callable)
    {
        return $this->filterInternal($callable, false);
    }

    private function filterInternal($callable, $booleanKeep)
    {
        $newElements = array();
        foreach ($this->elements as $k => $element) {
            if ($booleanKeep !== call_user_func($callable, $element)) {
                continue;
            }

            $newElements[$k] = $element;
        }

        return $this->createNew($newElements);
    }

    /**
     * Executes a function to 'fold' the map beginning from 
     * the first element until the last.
     * 
     * The function should be in the following form, $a will 
     * be the initial value and $b the first element. After the $a 
     * will be the result of the funtion and $b the next element.
     * 
     * function ($a, $b) {
     *    return $result;
     * }
     * 
     * @param mixed $initialValue
     * @param function $callable
     * @return mixed
     */
    public function foldLeft($initialValue, $callable)
    {
        $value = $initialValue;
        foreach ($this->elements as $elem) {
            $value = call_user_func($callable, $value, $elem);
        }

        return $value;
    }

    /**
     * Works the same as foldLeft but starts at the end and 
     * works until the beginning.
     * 
     * @param mixed $initialValue
     * @param function $callable
     * @return mixed
     */
    public function foldRight($initialValue, $callable)
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = call_user_func($callable, $elem, $value);
        }

        return $value;
    }

    public function dropWhile($callable)
    {
        $newElements = array();
        $stopped = false;
        foreach ($this->elements as $k => $v) {
            if ( ! $stopped) {
                if (call_user_func($callable, $k, $v) === true) {
                    continue;
                }

                $stopped = true;
            }

            $newElements[$k] = $v;
        }

        return $this->createNew($newElements);
    }

    public function drop($number)
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number, null, true));
    }

    public function dropRight($number)
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number, true));
    }

    public function take($number)
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number, true));
    }

    public function takeWhile($callable)
    {
        $newElements = array();
        foreach ($this->elements as $k => $v) {
            if (call_user_func($callable, $k, $v) !== true) {
                break;
            }

            $newElements[$k] = $v;
        }

        return $this->createNew($newElements);
    }

    public function find($callable)
    {
        foreach ($this->elements as $k => $v) {
            if (call_user_func($callable, $k, $v) === true) {
                return new Some(array($k, $v));
            }
        }

        return None::create();
    }

    public function keys()
    {
        return array_keys($this->elements);
    }

    public function values()
    {
        return array_values($this->elements);
    }

    public function count()
    {
        return count($this->elements);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    protected function createNew(array $elements)
    {
        return new static($elements);
    }

    public function offsetExists($offset) {
        return $this->containsKey($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        return $this->remove($offset);
    }

}