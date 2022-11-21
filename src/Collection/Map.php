<?php
namespace Collection;

use PhpOption\LazyOption;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

class Map implements MapInterface, SortableInterface, \JsonSerializable, \Countable, \IteratorAggregate
{
    protected array $elements;

    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return $this|MapInterface
     */
    public function set(int|string $key, mixed $value): self
    {
        $this->elements[$key] = $value;
        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return bool
     */
    public function exists(callable $callable): bool
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
     * @return $this|MapInterface
     */
    public function setAll(array $kvMap): self
    {
        foreach ($kvMap as $k => $v) {
            $this->set($k,$v);
        }

        return $this;
    }

    /**
     * @param MapInterface $map
     *
     * @return $this|MapInterface
     */
    public function addMap(MapInterface $map): self
    {
        foreach ($map as $k => $v) {
            $this->set($k,$v);
        }

        return $this;
    }

    /**
     * @param mixed $key
     *
     * @return Option
     */
    public function get(int|string|object $key): Option
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

    public function remove($key): mixed
    {
        if (!isset($this->elements[$key])) {
            throw new \InvalidArgumentException(sprintf('The map has no key named "%s".', $key));
        }

        $element = $this->elements[$key];
        unset($this->elements[$key]);

        return $element;
    }

    /**
     * @return $this|MapInterface
     */
    public function clear(): self
    {
        $this->elements = [];

        return $this;
    }

    /**
     * @return Option
     */
    public function headOption(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        $elem = reset($this->elements);
        $key = key($this->elements);

        return new Some([$key, $elem]);
    }

    public function head(): ?array
    {
        if (empty($this->elements)) {
            return null;
        }

        $elem = reset($this->elements);
        $key = key($this->elements);

        return [$key, $elem];
    }

    /**
     * @return $this|MapInterface
     */
    public function tail(): self
    {
        return new static(array_slice($this->elements, 1));
    }

    /**
     * @return null|array
     */
    public function last(): ?array
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
    public function lastOption(): Option
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
    public function contains($elem): bool
    {
        if (in_array($elem, $this->elements, true)) {
            return true;
        }

        return false;
    }

    public function containsKey($key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= keep), or false (= remove).
     *
     * @return $this|MapInterface
     */
    public function filter(callable $callable): self
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= remove), or false (= keep).
     *
     * @return $this|MapInterface
     */
    public function filterNot(callable $callable): self
    {
        return $this->filterInternal($callable, false);
    }

    /**
     * @param callable $callable
     * @param boolean  $keep
     *
     * @return $this|MapInterface
     */
    private function filterInternal(callable $callable, bool $keep): self
    {
        $newElements = [];
        foreach ($this->elements as $k => $e) {
            if ($keep !== $callable($k, $e)) {
                continue;
            }

            $newElements[$k] = $e;
        }

        return $this->createNew($newElements);
    }

    /**
     * @param mixed    $startValue
     * @param callable $callable
     * @return $this|MapInterface
     */
    public function foldLeft(mixed $startValue, callable $callable): mixed
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
     * @return $this|MapInterface
     */
    public function foldRight(mixed $startValue, callable $callable): mixed
    {
        $value = $startValue;
        $keys = array_keys($this->elements);
        foreach (array_reverse($keys) as $k) {
            $value = $callable($value, $k, $this->elements[$k]);
        }

        return $value;
    }

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     * @return $this|MapInterface
     */
    public function map(callable $callable): self
    {
        $newMap = new static;
        foreach ($this->elements as $k => $e) {
            $newMap->set($k, $callable($k, $e));
        }

        return $newMap;
    }

    /**
     * @param callable $callable:Map
     * @return $this|MapInterface
     */
    public function flatMap(callable $callable): self
    {
        $newMap = new static;
        foreach ($this->elements as $k => $e) {
            $newMap->addMap($callable($k, $e));
        }

        return $newMap;
    }

    /**
     * @param callable $callable
     * @return $this|MapInterface
     */
    public function dropWhile(callable $callable): self
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

    public function drop(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number, null, true));
    }

    public function dropRight(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number, true));
    }

    public function take(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number, true));
    }

    public function takeWhile(callable $callable): self
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

    public function find(callable $callable): LazyOption
    {
        $self = $this;

        return new LazyOption(
            function () use ($callable, $self) {
                foreach ($self as $k => $v) {
                    if (call_user_func($callable, $k, $v) === true) {
                        return new Some([$k, $v]);
                    }
                }

                return None::create();
            }
        );
    }

    public function sliding(int $size): Sequence
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

    public function keys(): array
    {
        return array_keys($this->elements);
    }

    public function values(): array
    {
        return array_values($this->elements);
    }

    public function length(): int
    {
        return count($this->elements);
    }

    /**
     * @return int
     * @deprecated Use ::length()
     */
    public function count(): int
    {
        return $this->length();
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    protected function createNew(array $elements): self
    {
        return new static($elements);
    }

    /**
     * @param $callable
     *
     * @return $this|MapInterface
     */
    public function sortWith(callable $callable): static
    {
        uksort($this->elements, $callable);

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->all();
    }
}
