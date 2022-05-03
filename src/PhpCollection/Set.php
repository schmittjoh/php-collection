<?php

namespace PhpCollection;

use PhpOption\None;
use PhpOption\Some;

/**
 * Implementation of a Set.
 *
 * Each set guarantees that equal elements are only contained once in the Set.
 *
 * This implementation constraints Sets to either consist of objects that implement ObjectBasics, or objects that have
 * an external ObjectBasicsHandler implementation, or simple scalars. These types cannot be mixed within the same Set.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Set implements SetInterface
{
    public const ELEM_TYPE_SCALAR = 1;
    public const ELEM_TYPE_OBJECT = 2;
    public const ELEM_TYPE_OBJECT_WITH_HANDLER = 3;

    private $elementType;

    private array $elements = [];
    private int $elementCount = 0;
    private array $lookup = [];

    public function __construct(array $elements = [])
    {
        $this->addAll($elements);
    }

    public function first(): Some|None
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(reset($this->elements));
    }

    public function last(): Some|None
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(end($this->elements));
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator(array_values($this->elements ?: []));
    }

    public function addSet(SetInterface $set): SetInterface
    {
        $this->addAll($set->all());

        return $this;
    }

    public function take($number): SetInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('$number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number));
    }

    /**
     * Extracts element from the head while the passed callable returns true.
     *
     * @param callable $callable receives elements of this Set as first argument, and returns true/false
     */
    public function takeWhile($callable): static
    {
        $newElements = [];

        for ($i = 0,$c = count($this->elements); $i < $c; $i++) {
            if (true !== call_user_func($callable, $this->elements[$i])) {
                break;
            }

            $newElements[] = $this->elements[$i];
        }

        return $this->createNew($newElements);
    }

    public function drop($number): SetInterface|static
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number));
    }

    public function dropRight($number): SetInterface|static
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number));
    }

    public function dropWhile($callable): static
    {
        for ($i = 0,$c = count($this->elements); $i < $c; $i++) {
            if (true !== call_user_func($callable, $this->elements[$i])) {
                break;
            }
        }

        return $this->createNew(array_slice($this->elements, $i));
    }

    public function map($callable): static
    {
        $newElements = [];
        foreach ($this->elements as $i => $element) {
            $newElements[$i] = $callable($element);
        }

        return $this->createNew($newElements);
    }

    public function reverse(): static
    {
        return $this->createNew(array_reverse($this->elements));
    }

    public function all(): array
    {
        return array_values($this->elements);
    }

    public function filterNot($callable): static
    {
        return $this->filterInternal($callable, false);
    }

    public function filter($callable): static
    {
        return $this->filterInternal($callable, true);
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

    public function addAll(array $elements): void
    {
        foreach ($elements as $elem) {
            $this->add($elem);
        }
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function contains(mixed $searchedElement): bool
    {
        if (self::ELEM_TYPE_OBJECT === $this->elementType) {
            if ($searchedElement instanceof ObjectBasics) {
                return $this->containsObject($searchedElement);
            }

            return false;
        } elseif (self::ELEM_TYPE_OBJECT_WITH_HANDLER === $this->elementType) {
            if (is_object($searchedElement)) {
                return $this->containsObjectWithHandler($searchedElement, ObjectBasicsHandlerRegistry::getHandler($searchedElement::class));
            }

            return false;
        } elseif (self::ELEM_TYPE_SCALAR === $this->elementType) {
            if (is_scalar($searchedElement)) {
                return $this->containsScalar($searchedElement);
            }

            return false;
        }

        return false;
    }

    public function remove(mixed $elem): void
    {
        if (self::ELEM_TYPE_OBJECT === $this->elementType) {
            if ($elem instanceof ObjectBasics) {
                $this->removeObject($elem);
            }
        } elseif (self::ELEM_TYPE_OBJECT_WITH_HANDLER === $this->elementType) {
            if (is_object($elem)) {
                $this->removeObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler($elem::class));
            }
        } elseif (self::ELEM_TYPE_SCALAR === $this->elementType) {
            if (is_scalar($elem)) {
                $this->removeScalar($elem);
            }
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function add(mixed $elem): void
    {
        if (null === $this->elementType) {
            if ($elem instanceof ObjectBasics) {
                $this->addObject($elem);
            } elseif (is_scalar($elem)) {
                $this->addScalar($elem);
            } else {
                if (is_object($elem)) {
                    $this->addObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler($elem::class));
                } else {
                    throw new \LogicException(sprintf('The type of $elem ("%s") is not supported in sets.', gettype($elem)));
                }
            }
        } elseif (self::ELEM_TYPE_OBJECT === $this->elementType) {
            if ($elem instanceof ObjectBasics) {
                $this->addObject($elem);

                return;
            }

            if (is_object($elem)) {
                throw new \LogicException(sprintf('This Set already contains object implement ObjectBasics, and cannot be mixed with objects that do not implement this interface like "%s".', $elem::class));
            }

            throw new \LogicException(sprintf('This Set already contains objects, and cannot be mixed with elements of type "%s".', gettype($elem)));
        } elseif (self::ELEM_TYPE_OBJECT_WITH_HANDLER === $this->elementType) {
            if (is_object($elem)) {
                $this->addObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler($elem::class));

                return;
            }

            throw new \LogicException(sprintf('This Set already contains object with an external handler, and cannot be mixed with elements of type "%s".', gettype($elem)));
        } elseif (self::ELEM_TYPE_SCALAR === $this->elementType) {
            if (is_scalar($elem)) {
                $this->addScalar($elem);

                return;
            }

            throw new \LogicException(sprintf('This Set already contains scalars, and cannot be mixed with elements of type "%s".', gettype($elem)));
        } else {
            throw new \LogicException('Unknown element type in Set - should never be reached.');
        }
    }

    protected function createNew(array $elements): static
    {
        return new static($elements);
    }

    private function filterInternal($callable, $booleanKeep): static
    {
        $newElements = [];
        foreach ($this->elements as $element) {
            if ($booleanKeep !== call_user_func($callable, $element)) {
                continue;
            }

            $newElements[] = $element;
        }

        return $this->createNew($newElements);
    }

    private function containsScalar($elem): bool
    {
        if (!isset($this->lookup[$elem])) {
            return false;
        }

        foreach ($this->lookup[$elem] as $index) {
            if ($elem === $this->elements[$index]) {
                return true;
            }
        }

        return false;
    }

    private function containsObjectWithHandler($object, ObjectBasicsHandler $handler): bool
    {
        $hash = $handler->hash($object);
        if (!isset($this->lookup[$hash])) {
            return false;
        }

        foreach ($this->lookup[$hash] as $index) {
            if ($handler->equals($object, $this->elements[$index])) {
                return true;
            }
        }

        return false;
    }

    private function containsObject(ObjectBasics $object): bool
    {
        $hash = $object->hash();
        if (!isset($this->lookup[$hash])) {
            return false;
        }

        foreach ($this->lookup[$hash] as $index) {
            if ($object->equals($this->elements[$index])) {
                return true;
            }
        }

        return false;
    }

    private function removeScalar($elem)
    {
        if (!isset($this->lookup[$elem])) {
            return;
        }

        foreach ($this->lookup[$elem] as $k => $index) {
            if ($elem === $this->elements[$index]) {
                $this->removeElement($elem, $k, $index);

                break;
            }
        }
    }

    private function removeObjectWithHandler($object, ObjectBasicsHandler $handler)
    {
        $hash = $handler->hash($object);
        if (!isset($this->lookup[$hash])) {
            return;
        }

        foreach ($this->lookup[$hash] as $k => $index) {
            if ($handler->equals($object, $this->elements[$index])) {
                $this->removeElement($hash, $k, $index);

                break;
            }
        }
    }

    private function removeObject(ObjectBasics $object)
    {
        $hash = $object->hash();
        if (!isset($this->lookup[$hash])) {
            return;
        }

        foreach ($this->lookup[$hash] as $k => $index) {
            if ($object->equals($this->elements[$index])) {
                $this->removeElement($hash, $k, $index);

                break;
            }
        }
    }

    private function removeElement($hash, $lookupIndex, $storageIndex)
    {
        unset($this->lookup[$hash][$lookupIndex]);
        if (empty($this->lookup[$hash])) {
            unset($this->lookup[$hash]);
        }

        unset($this->elements[$storageIndex]);
    }

    private function addScalar($elem)
    {
        if (isset($this->lookup[$elem])) {
            foreach ($this->lookup[$elem] as $index) {
                if ($this->elements[$index] === $elem) {
                    return; // Already exists.
                }
            }
        }

        $this->insertElement($elem, $elem);
        $this->elementType = self::ELEM_TYPE_SCALAR;
    }

    private function addObjectWithHandler($object, ObjectBasicsHandler $handler)
    {
        $hash = $handler->hash($object);
        if (isset($this->lookup[$hash])) {
            foreach ($this->lookup[$hash] as $index) {
                if ($handler->equals($object, $this->elements[$index])) {
                    return; // Already exists.
                }
            }
        }

        $this->insertElement($object, $hash);
        $this->elementType = self::ELEM_TYPE_OBJECT_WITH_HANDLER;
    }

    private function addObject(ObjectBasics $elem)
    {
        $hash = $elem->hash();
        if (isset($this->lookup[$hash])) {
            foreach ($this->lookup[$hash] as $index) {
                if ($elem->equals($this->elements[$index])) {
                    return; // Element already exists.
                }
            }
        }

        $this->insertElement($elem, $hash);
        $this->elementType = self::ELEM_TYPE_OBJECT;
    }

    private function insertElement($elem, $hash)
    {
        $index = $this->elementCount++;
        $this->elements[$index] = $elem;
        $this->lookup[$hash][] = $index;
    }
}
