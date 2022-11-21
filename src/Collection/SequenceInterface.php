<?php

namespace Collection;

use PhpOption\Option;

/**
 * Interface for mutable sequences.
 *
 * Equality of elements in the sequence is established via a shallow comparison (===).
 *
 * @author J. M. Schmitt, A. Sukharev
 */
interface SequenceInterface
{
    /**
     * @param SequenceInterface $seq
     *
     * @return $this|SequenceInterface
     */
    public function addSequence(SequenceInterface $seq): self;

    /**
     * @param mixed $searchedElement
     *
     * @return int
     */
    public function indexOf(mixed $searchedElement): int;

    /**
     * @param mixed $searchedElement
     *
     * @return int
     */
    public function lastIndexOf(mixed $searchedElement): int;

    public function head(): mixed;

    public function headOption(): Option;

    public function tail(): self;

    public function reverse(): self;

    public function isDefinedAt(int $index): bool;

    public function filter(callable $callable): self;

    public function contains(mixed $searchedElem): bool;

    public function find(callable $callable): Option;

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     */
    public function map(callable $callable): self;

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable Callable takes (x : \Traversable) => \Traversable
     *
     * @return $this|SequenceInterface
     */
    public function flatMap(callable $callable): self;

    /**
     * Returns a collection when any first level nesting is flattened into the single
     * returned collection
     *
     * @return $this|SequenceInterface
     */
    public function flatten(): self;

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (= remove) or false (= keep).
     *
     * @return $this|SequenceInterface
     */
    public function filterNot(callable $callable): self;

    /**
     * Applies a binary operator to a start value and all elements of this set, going left to right.
     * foldLeft[B](z: B, op: (B, A) ⇒ B): B
     *
     * B - the result type of the binary operator.
     * z - the start value.
     * op -the binary operator.
     *
     * @param mixed    $initialValue - the start value
     * @param callable $callable     - the binary operator
     *
     * @return mixed - the result of inserting op between consecutive elements of this set, going left to right with the start value z on the left:
     */
    public function foldLeft(mixed $initialValue, callable $callable): mixed;

    public function foldRight(mixed $initialValue, callable $callable): mixed;

    /**
     * Finds the first index where the given callable returns true.
     *
     * @param callable $callable
     *
     * @return int the index, or -1 if the predicate is not true for any element.
     */
    public function indexWhere(callable $callable): int;

    public function lastIndexWhere(callable $callable): int;

    public function last(): mixed;

    public function lastOption(): Option;

    /**
     * @return array
     */
    public function indices(): array;

    /**
     * Returns an element based on its index (0-based).
     *
     * @param integer $index
     *
     * @return Option
     */
    public function get(int $index): Option;

    /**
     * Removes the element at the given index, and returns it.
     *
     * @param int $index
     *
     * @return mixed
     *
     * @throws \OutOfBoundsException If there is no element at the given index.
     */
    public function remove(int $index): Option;

    /**
     * Updates the element at the given index (0-based).
     *
     * @param integer $index
     * @param mixed   $value
     *
     * @return $this|SequenceInterface
     */
    public function update(int $index, mixed $value): self;

    public function isEmpty(): bool;

    public function all(): array;

    public function add(mixed $newElement): self;

    public function addAll(iterable $values): self;

    public function take(int $number): self;

    /**
     * Extracts element from the head while the passed callable returns true.
     *
     * @param callable $callable receives elements of this sequence as first argument, and returns true/false.
     *
     * @return $this|SequenceInterface
     */
    public function takeWhile(callable $callable): self;

    public function drop(int $number): self;

    public function dropRight(int $number): self;

    public function dropWhile(callable $callable): self;

    public function sliding(int $size): Sequence;

    public function exists(callable $callable): bool;

    public function count(): int;

    public function length(): int;

    public function getIterator(): \ArrayIterator;
}