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

class LRUMap extends Map implements SortableInterface
{

    /** @var int */
    protected $maximumSize;

    /**
     * Create a LRU Map
     *
     * @param int $size
     * @throws \InvalidArgumentException
     */
    public function __construct($size)
    {
        if (!is_int($size) || $size <= 0) {
            throw new \InvalidArgumentException();
        }
        $this->maximumSize = $size;
        $this->elements = [];
    }

    /**
     * Get the value cached with this key
     *
     * @param int|string $key The key. Strings that are ints are cast to ints.
     *
     * @return \PhpOption\Option
     */
    public function get($key)
    {
        $this->recordAccess($key);

        return parent::get($key);
    }

    /**
     * Put something in the map
     *
     * @param int|string $key   The key. Strings that are ints are cast to ints.
     * @param mixed      $value The value to cache
     *
     * @return LRUMap
     */
    public function set($key, $value)
    {

        if (parent::get($key)->isDefined()) {
            parent::set($key, $value);

            $this->recordAccess($key);
        }
        else {
            parent::set($key, $value);
            if ($this->count() > $this->maximumSize) {
                // remove least recently used element (front of array)
                reset($this->elements);
                unset($this->elements[key($this->elements)]);
            }
        }

        return $this;
    }

    /**
     * Moves the element from current position to end of array
     *
     * @param int|string $key The key
     * @return LRUMap
     */
    protected function recordAccess($key)
    {
        foreach (parent::get($key) as $value) {
            unset($this->elements[$key]);
            $this->elements[$key] = $value;
        }

        return $this;
    }

    protected function createNew(array $elements)
    {
        $newMap = new static($this->maximumSize);
        $newMap->setAll($elements);

        return $newMap;
    }

}