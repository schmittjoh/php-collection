<?php
namespace Collection;

use PhpOption\Option;

/**
 * Interface for sets.
 *
 * Each Set contains equal values only once.
 *
 * @author J. M. Schmitt, A. Sukharev
 */
interface SetInterface extends \Countable, \IteratorAggregate
{
    public function addAll(iterable $elements): self;

    public function head(): null|int|string|object;

    public function tail(): self;

    /**
     * @return Option
     */
    public function headOption(): Option;

    public function last(): null|int|string|object;

    /**
     * @return Option
     */
    public function lastOption(): Option;

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator;


    /**
     * @param int $number
     *
     * @return SetInterface
     */
    public function take(int $number): self;

    /**
     * Extracts element from the head while the passed callable returns true.
     *
     * @param callable $callable receives elements of this Set as first argument, and returns true/false.
     *
     * @return SetInterface
     */
    public function takeWhile(callable $callable): self;

    /**
     * @param int $number
     *
     * @return SetInterface
     */
    public function drop(int $number): self;

    /**
     * @param int $number
     *
     * @return SetInterface
     */
    public function dropRight(int $number): self;

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function dropWhile(callable $callable): self;

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function map(callable $callable): self;

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function flatMap(callable $callable): self;

    /**
     * @return SetInterface
     */
    public function reverse(): self;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function filterNot(callable $callable): self;

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function filter(callable $callable): self;

    /**
     * @param mixed    $initialValue
     * @param callable $callable
     *
     * @return mixed
     */
    public function foldLeft(mixed $initialValue, callable $callable): mixed;

    /**
     * @param mixed    $initialValue
     * @param callable $callable
     *
     * @return mixed
     */
    public function foldRight(mixed $initialValue, callable $callable): mixed;

    /**
     * @param int $size
     *
     * @return SequenceInterface
     */
    public function sliding(int $size): Sequence;

    /**
     * @return int
     * @deprecated Use length()
     */
    public function count(): int;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @param $elem
     *
     * @return bool
     */
    public function contains(int|string|object $elem): bool;

    /**
     * @param mixed|object $elem
     *
     * @return SetInterface
     */
    public function remove(int|string|object $elem): self;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param mixed|object $elem
     *
     * @return SetInterface
     */
    public function add(int|string|object $elem): self;

    /**
     * @return array
     */
    public function jsonSerialize(): array;
}