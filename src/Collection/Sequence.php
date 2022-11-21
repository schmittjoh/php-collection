<?php
namespace Collection;

use PhpOption\LazyOption;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

/**
 * Unsorted sequence implementation.
 *
 * Characteristics:
 *
 *     - Keys: consequentially numbered, without gaps
 *     - Values: anything, duplicates allowed
 *     - Ordering: same as input unless when explicitly sorted
 *
 * @author J. M. Schmitt, A. Sukharev
 */
class Sequence implements SequenceInterface, SortableInterface, \JsonSerializable, \Countable, \IteratorAggregate
{
    protected array $elements;
    protected int   $length;

    /**
     * @param array $elements
     */
    public function __construct(iterable $elements = [])
    {
        $this->elements = [];
        $this->length = 0;

        $this->addAll($elements);
    }

    /**
     * @param SequenceInterface $seq
     *
     * @return $this|SequenceInterface
     */
    public function addSequence(SequenceInterface $seq): self
    {
        $this->addAll($seq->all());

        return $this;
    }

    /**
     * @param mixed $searchedElement
     *
     * @return int
     */
    public function indexOf(mixed $searchedElement): int
    {
        foreach ($this->elements as $i => $element) {
            if ($searchedElement === $element) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @param mixed $searchedElement
     *
     * @return int
     */
    public function lastIndexOf(mixed $searchedElement): int
    {
        for ($i = $this->length - 1; $i >= 0; $i--) {
            if ($this->elements[$i] === $searchedElement) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @return mixed|null
     */
    public function head(): mixed
    {
        if (empty($this->elements)) {
            return null;
        }

        return reset($this->elements);
    }

    /**
     * @return Option
     */
    public function headOption(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(reset($this->elements));
    }

    /**
     * @return $this|SequenceInterface
     */
    public function tail(): self
    {
        return $this->createNew(array_slice($this->elements, 1));
    }

    /**
     * @return $this|SequenceInterface
     */
    public function reverse(): self
    {
        return $this->createNew(array_reverse($this->elements));
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function isDefinedAt(int $index): bool
    {
        return isset($this->elements[$index]);
    }

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (= keep) or false (= remove).
     *
     * @return $this|SequenceInterface
     */
    public function filter(callable $callable): self
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * @param $searchedElem
     *
     * @return bool
     */
    public function contains(mixed $searchedElem): bool
    {
        foreach ($this as $elem) {
            if ($elem === $searchedElem) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable $callable
     *
     * @return LazyOption
     */
    public function find(callable $callable): Option
    {
        $self = $this;

        return new LazyOption(
            function () use ($callable, $self) {
                foreach ($self as $elem) {
                    if (call_user_func($callable, $elem) === true) {
                        return new Some($elem);
                    }
                }

                return None::create();
            }
        );
    }

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     *
     * @return $this|SequenceInterface
     */
    public function map(callable $callable): self
    {
        $newElements = [];
        foreach ($this->elements as $i => $element) {
            $newElements[$i] = $callable($element);
        }

        return $this->createNew($newElements);
    }

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable Callable takes (x : \Traversable) => \Traversable
     *
     * @return $this|SequenceInterface
     */
    public function flatMap(callable $callable): self
    {
        return $this->map($callable)->flatten();
    }

    /**
     * Returns a collection when any first level nesting is flattened into the single
     * returned collection
     *
     * @return $this|SequenceInterface
     */
    public function flatten(): self
    {
        $res = $this->createNew([]);

        foreach ($this->elements as $elem) {
            $res->addAll($elem);
        }

        return $res;
    }

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (= remove) or false (= keep).
     *
     * @return $this|SequenceInterface
     */
    public function filterNot(callable $callable): self
    {
        return $this->filterInternal($callable, false);
    }

    private function filterInternal(callable $callable, bool $keep): self
    {
        $newElements = [];
        foreach ($this->elements as $element) {
            if ($keep !== $callable($element)) {
                continue;
            }

            $newElements[] = $element;
        }

        return $this->createNew($newElements);
    }

    /**
     * Applies a binary operator to a start value and all elements of this set, going left to right.
     * foldLeft[B](z: B, op: (B, A) ⇒ B): B
     *
     * B - the result type of the binary operator.
     * z - the start value.
     * op -the binary operator.
     *
     * @param mixed $initialValue - the start value
     * @param callable $callable  - the binary operator
     *
     * @return mixed - the result of inserting op between consecutive elements of this set, going left to right with the start value z on the left:
     */
    public function foldLeft(mixed $initialValue, callable $callable): mixed
    {
        $value = $initialValue;
        foreach ($this->elements as $elem) {
            $value = $callable($value, $elem);
        }

        return $value;
    }

    public function foldRight(mixed $initialValue, callable $callable): mixed
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = $callable($value, $elem);
        }

        return $value;
    }

    /**
     * Finds the first index where the given callable returns true.
     *
     * @param callable $callable
     *
     * @return integer the index, or -1 if the predicate is not true for any element.
     */
    public function indexWhere(callable $callable): int
    {
        foreach ($this->elements as $i => $element) {
            if ($callable($element) === true) {
                return $i;
            }
        }

        return -1;
    }

    public function lastIndexWhere(callable $callable): int
    {
        for ($i = $this->length - 1; $i >= 0; $i--) {
            if ($callable($this->elements[$i]) === true) {
                return $i;
            }
        }

        return -1;
    }

    public function last(): mixed
    {
        if (empty($this->elements)) {
            return null;
        }

        return end($this->elements);
    }

    public function lastOption(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(end($this->elements));
    }

    /**
     * @return array
     */
    public function indices(): array
    {
        return array_keys($this->elements);
    }

    /**
     * Returns an element based on its index (0-based).
     *
     * @param integer $index
     *
     * @return Option
     */
    public function get(int $index): Option
    {
        if (!isset($this->elements[$index])) {
            return None::create();
        }

        return new Some($this->elements[$index]);
    }

    /**
     * Removes the element at the given index, and returns it.
     *
     * @param int $index
     *
     * @return Option
     */
    public function remove(int $index): Option
    {
        if (!isset($this->elements[$index])) {
            return None::create();
        }

        $element = $this->elements[$index];

        unset($this->elements[$index]);
        $this->length--;

        $this->elements = array_values($this->elements);

        return new Some($element);
    }

    /**
     * Updates the element at the given index (0-based).
     *
     * @param integer $index
     * @param mixed   $value
     *
     * @return $this|SequenceInterface
     * @throws \OutOfBoundsException
     */
    public function update(int $index, mixed $value): self
    {
        if (!array_key_exists($index, $this->elements)) {
            throw new \OutOfBoundsException(sprintf('no element at index "%d".', $index));
        }

        $this->elements[$index] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * @param mixed $newElement
     *
     * @return $this|SequenceInterface
     */
    public function add(mixed $newElement): self
    {
        $this->elements[] = $newElement;
        $this->length++;

        return $this;
    }

    /**
     * @param iterable $elements
     *
     * @return $this|SequenceInterface
     */
    public function addAll(iterable $values): self
    {
        foreach ($values as $e) {
            $this->add($e);
        }

        return $this;
    }

    /**
     * @param int $number
     *
     * @return $this|SequenceInterface
     */
    public function take(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('$number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number));
    }

    /**
     * Extracts element from the head while the passed callable returns true.
     *
     * @param callable $callable receives elements of this sequence as first argument, and returns true/false.
     *
     * @return $this|SequenceInterface
     */
    public function takeWhile(callable $callable): self
    {
        $newElements = [];

        foreach ($this->elements as $v) {
            if ($callable($v) !== true) {
                break;
            }

            $newElements[] = $v;
        }

        return $this->createNew($newElements);
    }

    /**
     * @param int $number
     *
     * @return $this|SequenceInterface
     */
    public function drop(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number));
    }

    /**
     * @param int $number
     *
     * @return $this|SequenceInterface
     */
    public function dropRight(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number));
    }

    /**
     * @param callable $callable
     *
     * @return $this|SequenceInterface
     */
    public function dropWhile(callable $callable): self
    {
        for ($i = 0; $i < $this->length; $i++) {
            if (true !== $callable($this->elements[$i])) {
                break;
            }
        }

        return $this->createNew(array_slice($this->elements, $i));
    }

    /**
     * @param int $size
     *
     * @return $this|SequenceInterface
     */
    public function sliding(int $size): Sequence
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException(
                sprintf('The number must be greater than 0, but got %d.', $size)
            );
        }

        $slices = new Sequence();

        $offset = 0;
        while ($offset < $this->length()) {
            $slices->add($this->createNew(array_slice($this->elements, $offset, $size)));
            $offset += $size;
        }

        return $slices;
    }

    /**
     * @param callable $callable
     *
     * @return bool
     */
    public function exists(callable $callable): bool
    {
        foreach ($this as $elem) {
            if ($callable($elem) === true) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return $this->length();
    }

    public function length(): int
    {
        return $this->length;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @param $elements
     *
     * @return $this|SequenceInterface
     */
    protected function createNew(iterable $elements): self
    {
        return new static($elements);
    }

    /**
     * @param $callable
     *
     * @return SequenceInterface
     */
    public function sortWith(callable $callable): self
    {
        usort($this->elements, $callable);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }
}
