<?php

namespace Collection;

use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use function PHPUnit\Framework\throwException;

/**
 * Implementation of a Set.
 *
 * Each set guarantees that equal elements are only contained once in the Set.
 *
 * This implementation constraints Sets to either consist of objects that implement ObjectBasics, or objects that have
 * an external ObjectBasicsHandler implementation, or simple scalars. These types cannot be mixed within the same Set.
 *
 * @author J. M. Schmitt, A. Sukharev
 */
class Set implements SetInterface, \JsonSerializable
{
    private array $elements     = [];
    private int   $elementCount = 0;
    private array $lookup       = [];

    public function __construct(array $elements = [])
    {
        $this->addAll($elements);
    }

    /**
     * @param iterable $elements
     *
     * @return SetInterface
     */
    public function addAll(iterable|SetInterface $elements): self
    {
        if ($elements instanceof SetInterface) {
            $elements = $elements->all();
        }

        foreach ($elements as $elem) {
            $this->add($elem);
        }

        return $this;
    }

    public function head(): null|int|string|object
    {
        if (empty($this->elements)) {
            return null;
        }

        return reset($this->elements);
    }

    public function tail(): self
    {
        return $this->createNew(array_slice($this->elements, 1));
    }

    /**
     * @return None|Some
     */
    public function headOption(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(reset($this->elements));
    }

    public function last(): null|int|string|object
    {
        if (empty($this->elements)) {
            return null;
        }

        return end($this->elements);
    }

    /**
     * @return None|Some
     */
    public function lastOption(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(end($this->elements));
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_values($this->elements));
    }

    /**
     * @param int $number
     *
     * @return SetInterface
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
     * @param callable $callable receives elements of this Set as first argument, and returns true/false.
     *
     * @return SetInterface
     */
    public function takeWhile(callable $callable): self
    {
        $newElements = [];

        for ($i = 0, $c = $this->length(); $i < $c; $i++) {
            if ($callable($this->elements[$i]) !== true) {
                break;
            }

            $newElements[] = $this->elements[$i];
        }

        return $this->createNew($newElements);
    }

    /**
     * @param int $number
     *
     * @return SetInterface
     */
    public function drop(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(
                sprintf('The number must be greater than 0, but got %d.', $number)
            );
        }

        return $this->createNew(array_slice($this->elements, $number));
    }

    /**
     * @param int $number
     *
     * @return SetInterface
     */
    public function dropRight(int $number): self
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(
                sprintf('The number must be greater than 0, but got %d.', $number)
            );
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number));
    }

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function dropWhile(callable $callable): self
    {
        for ($i = 0, $c = count($this->elements); $i < $c; $i++) {
            if (true !== $callable($this->elements[$i])) {
                break;
            }
        }

        return $this->createNew(array_slice($this->elements, $i));
    }

    /**
     * @param callable $callable
     *
     * @return SetInterface
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
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function flatMap(callable $callable): self
    {
        $newElements = new Set();
        foreach ($this->elements as $element) {
            $newElements->addAll($callable($element));
        }

        return $newElements;
    }

    /**
     * @return SetInterface
     */
    public function reverse(): self
    {
        return $this->createNew(array_reverse($this->elements));
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return array_values($this->elements);
    }

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function filterNot(callable $callable): self
    {
        return $this->filterInternal($callable, false);
    }

    /**
     * @param callable $callable
     *
     * @return SetInterface
     */
    public function filter(callable $callable): self
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * @param mixed    $initialValue
     * @param callable $callable
     *
     * @return mixed
     */
    public function foldLeft(mixed $initialValue, callable $callable): mixed
    {
        $value = $initialValue;
        foreach ($this->elements as $elem) {
            $value = $callable($value, $elem);
        }

        return $value;
    }

    /**
     * @param mixed    $initialValue
     * @param callable $callable
     *
     * @return mixed
     */
    public function foldRight(mixed $initialValue, callable $callable): mixed
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = $callable($elem, $value);
        }

        return $value;
    }

    /**
     * @param int $size
     *
     * @return SequenceInterface
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

    public function count(): int
    {
        return $this->length();
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->elementCount;
    }

    /**
     * @param $elem
     *
     * @return bool
     */
    public function contains(mixed $elem): bool
    {
        $lookup = $this->getValueHash($elem);

        return array_key_exists($lookup, $this->lookup);
    }

    /**
     * @param mixed|object $elem
     *
     * @return SetInterface
     */
    public function remove(int|string|object $elem): self
    {
        $lookup = $this->getValueHash($elem);
        if (!array_key_exists($lookup, $this->lookup)) {
            return $this;
        }

        $this->removeElement($lookup, $this->lookup[$lookup]);

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
     * @param mixed|object $elem
     *
     * @return SetInterface
     */
    public function add(int|string|object $elem): self
    {
        $lookup = $this->getValueHash($elem);

        if (array_key_exists($lookup, $this->lookup)) {
            return $this;
        }

        $this->insertElement($elem, $lookup);

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    /**
     * @param array $elements
     *
     * @return SetInterface
     */
    protected function createNew(array $elements): self
    {
        return new static($elements);
    }

    protected function getValueHash(int|string|object $value): string
    {
        if (is_scalar($value)) {
            return (string)$value;
        }

        if ($value instanceof HashableInterface) {
            return $value->hash();
        }

        if (is_object($value)) {
            return spl_object_hash($value);
        }

        throw new \InvalidArgumentException("Set support scalar values and objects only");
    }

    /**
     * @param callable $callable
     * @param          $keep
     *
     * @return SetInterface
     */
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

    private function removeElement(string $hash, int $storageIndex): self
    {
        unset($this->lookup[$hash]);
        unset($this->elements[$storageIndex]);
        $this->elementCount--;

        return $this;
    }

    private function insertElement(mixed $elem, string $hash): self
    {
        $index = $this->elementCount++;
        $this->elements[$index] = $elem;
        $this->lookup[$hash] = $index;

        return $this;
    }
}
