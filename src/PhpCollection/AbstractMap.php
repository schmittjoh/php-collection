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

use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

/**
 * A simple map implementation which basically wraps an array with an object oriented interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AbstractMap extends AbstractCollection implements \IteratorAggregate, MapInterface
{
    /**
     * @var array
     */
    protected array $elements;

    /**
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    public function set(mixed $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }

    public function exists(\Closure $callable): bool
    {
        foreach ($this as $k => $v) {
            if (true === $callable($k, $v)) {
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

    public function addMap(MapInterface $map): MapInterface
    {
        foreach ($map as $k => $v) {
            $this->elements[$k] = $v;
        }

        return $this;
    }

    public function get($key): Some|None
    {
        if (isset($this->elements[$key])) {
            return new Some($this->elements[$key]);
        }

        return None::create();
    }

    public function all(): array
    {
        return $this->elements;
    }

    public function remove(mixed $key): mixed
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
    }

    public function first(): Some|None
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = reset($this->elements);

        return new Some([key($this->elements), $elem]);
    }

    public function last(): Some|None
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = end($this->elements);

        return new Some([key($this->elements), $elem]);
    }

    public function contains(mixed $searchedElement): bool
    {
        foreach ($this->elements as $existingElem) {
            if ($existingElem === $searchedElement) {
                return true;
            }
        }

        return false;
    }

    public function containsKey(mixed $key): bool
    {
        return isset($this->elements[$key]);
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= keep), or false (= remove)
     *
     * @return MapInterface
     */
    public function filter($callable): MapInterface
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= remove), or false (= keep)
     *
     * @return MapInterface
     */
    public function filterNot($callable): MapInterface
    {
        return $this->filterInternal($callable, false);
    }

    public function foldLeft(mixed $initialValue, \Closure $callable): mixed
    {
        $value = $initialValue;
        foreach ($this->elements as $elem) {
            $value = call_user_func($callable, $value, $elem);
        }

        return $value;
    }

    public function foldRight(mixed $initialValue, \Closure $callable): mixed
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = call_user_func($callable, $elem, $value);
        }

        return $value;
    }

    public function dropWhile(\Closure $callable): MapInterface
    {
        $newElements = [];
        $stopped = false;
        foreach ($this->elements as $k => $v) {
            if (!$stopped) {
                if (true === call_user_func($callable, $k, $v)) {
                    continue;
                }

                $stopped = true;
            }

            $newElements[$k] = $v;
        }

        return $this->createNew($newElements);
    }

    public function drop(int $number): MapInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number, null, true));
    }

    public function dropRight($number): MapInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number, true));
    }

    public function take(int $number): MapInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number, true));
    }

    public function takeWhile($callable): MapInterface
    {
        $newElements = [];
        foreach ($this->elements as $k => $v) {
            if (true !== call_user_func($callable, $k, $v)) {
                break;
            }

            $newElements[$k] = $v;
        }

        return $this->createNew($newElements);
    }

    public function find(\Closure $callable): Option
    {
        foreach ($this->elements as $k => $v) {
            if (true === call_user_func($callable, $k, $v)) {
                return new Some([$k, $v]);
            }
        }

        return None::create();
    }

    public function keys(): array
    {
        return array_keys($this->elements);
    }

    public function values(): array
    {
        return array_values($this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements ?: []);
    }

    protected function createNew(array $elements): MapInterface
    {
        return new static($elements);
    }

    /**
     * @param callable $callable
     * @param bool $booleanKeep
     */
    private function filterInternal($callable, $booleanKeep)
    {
        $newElements = [];
        foreach ($this->elements as $k => $element) {
            if ($booleanKeep !== call_user_func($callable, $element)) {
                continue;
            }

            $newElements[$k] = $element;
        }

        return $this->createNew($newElements);
    }
}
