<?php
namespace Collection;

use PhpOption\Option;
use PhpOption\Some;
use PhpOption\None;

/**
 * A simple map implementation which basically wraps an array with an object oriented interface.
 *
 * @author Artyom Sukharev , J. M. Schmitt
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
     * @return MapInterface
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
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
     * @return MapInterface
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

    /**
     * @return MapInterface
     */
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
        $key = key($this->elements);

        return new Some([$key, $elem]);
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
     * @return MapInterface
     */
    public function tail()
    {
        return new static(array_slice($this->elements, 1));
    }

    /**
     * @return null|array
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
     * @return Option
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

    /**
     * @param $elem
     * @return bool
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
     * @return MapInterface
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
     * @return MapInterface
     */
    public function filterNot(callable $callable)
    {
        return $this->filterInternal($callable, false);
    }

    /**
     * @param callable $callable
     * @param boolean  $booleanKeep
     * @return MapInterface
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
     * @return MapInterface
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
     * @return MapInterface
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
     * @return MapInterface
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
     * @return MapInterface
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
     * @return MapInterface
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

    /**
     * @param int $size
     * @return SequenceInterface<MapInterface<A>>
     */
    public function sliding($size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $size));
        }

        $slices = new Sequence();

        $offset = 0;
        while ($offset < $this->length()) {
            $slices->add(new static(array_slice($this->elements, $offset, $size)));
            $offset += $size;
        }

        return $slices;
    }

    public function keys()
    {
        return array_keys($this->elements);
    }

    public function values()
    {
        return array_values($this->elements);
    }

    public function length()
    {
        return count($this->elements);
    }

    /**
     * @return int
     * @deprecated Use ::length()
     */
    public function count()
    {
        return $this->length();
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
