<?php

/*
 * Copyright 2016 Johannes M. Schmitt, Artyom Sukharev <aly.casus@gmail.com>
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

namespace Collection;

use PhpOption\Some;
use PhpOption\None;

/**
 * A simple map implementation which basically wraps an array with an object oriented interface.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
class AbstractMap extends AbstractCollection implements \IteratorAggregate, MapInterface
{
    protected $elements;

    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;
        return $this;
    }

    public function exists(callable $callable)
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
     * @return $this
     */
    public function setAll(array $kvMap)
    {
        foreach ($kvMap as $k => $v) {
            $this->set($k,$v);
        }

        return $this;
    }

    public function addMap(MapInterface $map)
    {
        foreach ($map as $k => $v) {
            $this->set($k,$v);
        }

        return $this;
    }

    /**
     * @param mixed $key
     *
     * @return \PhpOption\Option
     */
    public function get($key)
    {
        if (isset($this->elements[$key])) {
            return new Some($this->elements[$key]);
        }

        return None::create();
    }

    public function all()
    {
        return $this->elements;
    }

    public function remove($key)
    {
        if (!isset($this->elements[$key])) {
            throw new \InvalidArgumentException(sprintf('The map has no key named "%s".', $key));
        }

        $element = $this->elements[$key];
        unset($this->elements[$key]);

        return $element;
    }

    public function clear()
    {
        $this->elements = [];

        return $this;
    }

    /**
     * @return Some
     */
    public function headOption()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = reset($this->elements);

        return new Some([key($this->elements), $elem]);
    }

    /**
     * @return null|array
     */
    public function head()
    {
        if (empty($this->elements)) {
            return null;
        }

        $elem = reset($this->elements);
        $key = key($this->elements);

        return [$key, $elem];
    }

    /**
     * @return AbstractMap
     */
    public function tail()
    {
        return new static(array_slice($this->elements, 1));
    }


    /**
     * @return array
     */
    public function last()
    {
        if (empty($this->elements)) {
            return null;
        }

        $elem = end($this->elements);
        $key = key($this->elements);

        return [$key, $elem];
    }

    /**
     * @return None|Some
     */
    public function lastOption()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = end($this->elements);
        $key = key($this->elements);

        return new Some([$key, $elem]);
    }

    public function contains($elem)
    {
        foreach ($this->elements as $existingElem) {
            if ($existingElem === $elem) {
                return true;
            }
        }

        return false;
    }

    public function containsKey($key)
    {
        return array_key_exists($key, $this->elements);
    }

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
    public function filter(callable $callable)
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
    public function filterNot(callable $callable)
    {
        return $this->filterInternal($callable, false);
    }

    /**
     * @param callable $callable
     * @param boolean  $booleanKeep
     * @return Map
     */
    private function filterInternal(callable $callable, $booleanKeep)
    {
        $newElements = [];
        foreach ($this->elements as $k => $e) {
            if ($booleanKeep !== $callable($k, $e)) {
                continue;
            }

            $newElements[$k] = $e;
        }

        return $this->createNew($newElements);
    }

    /**
     * @param mixed    $startValue
     * @param callable $callable
     * @return Map
     */
    public function foldLeft($startValue, callable $callable)
    {
        $value = $startValue;
        foreach ($this->elements as $k => $e) {
            $value = $callable($value, $k, $e);
        }

        return $value;
    }

    /**
     * @param mixed    $startValue
     * @param callable $callable
     * @return Map
     */
    public function foldRight($startValue, callable $callable)
    {
        $value = $startValue;
        $keys = array_keys($this->elements);
        foreach (array_reverse($keys) as $k) {
            $value = $callable($value, $k, $this->elements[$k]);
        }

        return $value;
    }

    /**
     * @param callable $callable
     * @return AbstractMap
     */
    public function map(callable $callable)
    {
        $newMap = new static;
        foreach ($this->elements as $k => $e) {
            $newMap->set($k, $callable($k, $e));
        }

        return $newMap;
    }
    
    /**
     * @param callable $callable:Map
     * @return AbstractMap
     */
    public function flatMap(callable $callable)
    {
        $newMap = new static;
        foreach ($this->elements as $k => $e) {
            $newMap->addMap($callable($k, $e));
        }

        return $newMap;
    }

    /**
     * @param callable $callable
     * @return Map
     */
    public function dropWhile(callable $callable)
    {
        $newElements = [];
        $stopped = false;
        foreach ($this->elements as $k => $v) {
            if (!$stopped) {
                if ($callable($k, $v) === true) {
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

    public function takeWhile(callable $callable)
    {
        $newElements = [];
        foreach ($this->elements as $k => $v) {
            if ($callable($k, $v) !== true) {
                break;
            }

            $newElements[$k] = $v;
        }

        return $this->createNew($newElements);
    }

    public function find(callable $callable)
    {
        foreach ($this->elements as $k => $v) {
            if (call_user_func($callable, $k, $v) === true) {
                return new Some([$k, $v]);
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
}
