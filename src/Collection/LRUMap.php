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
            if ($this->length() > $this->maximumSize) {
                // remove least recently used element (front of array)
                reset($this->elements);
                unset($this->elements[key($this->elements)]);
            }
        }

        return $this;
    }

    /**
     * @return MapInterface
     */
    public function tail()
    {
        return $this->createNew(array_slice($this->elements, 1));
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
