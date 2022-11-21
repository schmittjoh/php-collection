<?php

namespace Collection;

use PhpOption\Option;

/**
 * Basic map interface.
 *
 * @author J. M. Schmitt, A. Sukharev
 */
interface MapInterface
{
    public function set(int|string $key, mixed $value): self;

    public function exists(callable $callable): bool;

    /**
     * Sets all key/value pairs in the map.
     */
    public function setAll(array $kvMap): self;

    public function addMap(MapInterface $map): self;

    public function get(int|string|object $key): Option;

    public function all(): array;

    public function remove($key): mixed;

    public function clear(): self;

    public function headOption(): Option;

    public function head(): ?array;

    public function tail(): self;

    public function last(): ?array;

    public function lastOption(): Option;

    public function contains($elem): bool;

    public function containsKey($key): bool;

    public function isEmpty(): bool;

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= keep), or false (= remove).
     *
     * @return $this|MapInterface
     */
    public function filter(callable $callable): self;

    /**
     * Returns a new filtered map.
     *
     * @param callable $callable receives the element and must return true (= remove), or false (= keep).
     *
     * @return $this|MapInterface
     */
    public function filterNot(callable $callable): self;

    public function foldLeft(mixed $startValue, callable $callable): mixed;

    public function foldRight(mixed $startValue, callable $callable): mixed;

    /**
     * Builds a new collection by applying a function to all elements of this map.
     *
     * @param callable $callable receives the element, and the current value (the first time this equals $initialValue).
     *
     * @return $this|MapInterface
     */
    public function map(callable $callable): self;

    public function flatMap(callable $callable): self;

    public function dropWhile(callable $callable): self;

    public function drop(int $number): self;

    public function dropRight(int $number): self;

    public function take(int $number): self;

    public function takeWhile(callable $callable): self;

    public function find(callable $callable): Option;

    public function sliding(int $size): Sequence;

    public function keys(): array;

    public function values(): array;

    public function length(): int;

    /**
     * @return int
     * @deprecated Use ::length()
     */
    public function count(): int;

    public function getIterator(): \ArrayIterator;
}