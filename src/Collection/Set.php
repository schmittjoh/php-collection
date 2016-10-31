<?php

namespace Collection;

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
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
class Set implements SetInterface
{
    const ELEM_TYPE_SCALAR = 1;
    const ELEM_TYPE_OBJECT = 2;
    const ELEM_TYPE_OBJECT_WITH_HANDLER = 3;

    private $elementType;

    private $elements = [];
    private $elementCount = 0;
    private $lookup = [];

    public function __construct(array $elements = [])
    {
        $this->addAll($elements);
    }

    /**
     * @param \Traversable|array $elements
     * @return $this
     */
    public function addAll($elements)
    {
        if(is_array($elements) or ($elements instanceof \Traversable)){
            foreach ($elements as $elem) {
                $this->add($elem);
            }
        }
        else {
            $this->add($elements);
        }

        return $this;
    }

    /**
     * @return None|Some
     */
    public function first()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(reset($this->elements));
    }

    /**
     * @return None|Some
     */
    public function last()
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(end($this->elements));
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->elements));
    }

    /**
     * @param SetInterface $set
     * @return $this
     */
    public function addSet(SetInterface $set)
    {
        $this->addAll($set);

        return $this;
    }

    public function take($number)
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
     * @return Set
     */
    public function takeWhile(callable $callable)
    {
        $newElements = [];

        for ($i = 0, $c = count($this->elements); $i < $c; $i++) {
            if ($callable($this->elements[$i]) !== true) {
                break;
            }

            $newElements[] = $this->elements[$i];
        }

        return $this->createNew($newElements);
    }

    public function drop($number)
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number));
    }

    public function dropRight($number)
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number));
    }

    /**
     * @param callable $callable
     * @return Set
     */
    public function dropWhile(callable $callable)
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
     * @return Set
     */
    public function map(callable $callable)
    {
        $newElements = [];
        foreach ($this->elements as $i => $element) {
            $newElements[$i] = $callable($element);
        }

        return $this->createNew($newElements);
    }

    /**
     * @param callable $callable
     * @return Set
     */
    public function flatMap(callable $callable)
    {
        $newElements = new Set();
        foreach ($this->elements as $i => $element) {
            $newElements->addAll($callable($element));
        }

        return $newElements;
    }

    public function reverse()
    {
        return $this->createNew(array_reverse($this->elements));
    }

    public function all()
    {
        return array_values($this->elements);
    }

    public function filterNot(callable $callable)
    {
        return $this->filterInternal($callable, false);
    }

    public function filter(callable $callable)
    {
        return $this->filterInternal($callable, true);
    }

    /**
     * @param mixed    $initialValue
     * @param callable $callable
     * @return mixed
     */
    public function foldLeft($initialValue, callable $callable)
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
     * @return mixed
     */
    public function foldRight($initialValue, callable $callable)
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = $callable($elem, $value);
        }

        return $value;
    }

    public function count()
    {
        return $this->elementCount;
    }

    public function contains($elem)
    {
        if ($this->elementType === self::ELEM_TYPE_OBJECT) {
            if ($elem instanceof ObjectBasics) {
                return $this->containsObject($elem);
            }

            return false;
        }
        else if ($this->elementType === self::ELEM_TYPE_OBJECT_WITH_HANDLER) {
            if (is_object($elem)) {
                return $this->containsObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler(get_class($elem)));
            }

            return false;
        }
        else if ($this->elementType === self::ELEM_TYPE_SCALAR) {
            if (is_scalar($elem)) {
                return $this->containsScalar($elem);
            }

            return false;
        }

        return false;
    }

    public function remove($elem)
    {
        if ($this->elementType === self::ELEM_TYPE_OBJECT) {
            if ($elem instanceof ObjectBasics) {
                $this->removeObject($elem);
            }
        }
        else if ($this->elementType === self::ELEM_TYPE_OBJECT_WITH_HANDLER) {
            if (is_object($elem)) {
                $this->removeObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler(get_class($elem)));
            }
        }
        else if ($this->elementType === self::ELEM_TYPE_SCALAR) {
            if (is_scalar($elem)) {
                $this->removeScalar($elem);
            }
        }

        return $this;
    }

    public function isEmpty()
    {
        return empty($this->elements);
    }

    public function add($elem)
    {
        if ($this->elementType === null) {
            if ($elem instanceof ObjectBasics) {
                $this->addObject($elem);
            }
            else if (is_scalar($elem)) {
                $this->addScalar($elem);
            }
            else {
                if (is_object($elem)) {
                    $this->addObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler(get_class($elem)));
                }
                else {
                    throw new \LogicException(sprintf('The type of $elem ("%s") is not supported in sets.', gettype($elem)));
                }
            }
        }
        else if ($this->elementType === self::ELEM_TYPE_OBJECT) {
            if ($elem instanceof ObjectBasics) {
                $this->addObject($elem);

                return $this;
            }

            if (is_object($elem)) {
                throw new \LogicException(sprintf('This Set already contains object implement ObjectBasics, and cannot be mixed with objects that do not implement this interface like "%s".', get_class($elem)));
            }

            throw new \LogicException(sprintf('This Set already contains objects, and cannot be mixed with elements of type "%s".', gettype($elem)));
        }
        else if ($this->elementType === self::ELEM_TYPE_OBJECT_WITH_HANDLER) {
            if (is_object($elem)) {
                $this->addObjectWithHandler($elem, ObjectBasicsHandlerRegistry::getHandler(get_class($elem)));

                return $this;
            }

            throw new \LogicException(sprintf('This Set already contains object with an external handler, and cannot be mixed with elements of type "%s".', gettype($elem)));
        }
        else if ($this->elementType === self::ELEM_TYPE_SCALAR) {
            if (is_scalar($elem)) {
                $this->addScalar($elem);

                return $this;
            }

            throw new \LogicException(sprintf('This Set already contains scalars, and cannot be mixed with elements of type "%s".', gettype($elem)));
        }
        else {
            throw new \LogicException('Unknown element type in Set - should never be reached.');
        }

        return $this;
    }

    protected function createNew(array $elements)
    {
        return new static($elements);
    }

    private function filterInternal(callable $callable, $booleanKeep)
    {
        $newElements = [];
        foreach ($this->elements as $element) {
            if ($booleanKeep !== $callable($element)) {
                continue;
            }

            $newElements[] = $element;
        }

        return $this->createNew($newElements);
    }

    private function containsScalar($elem)
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

    private function containsObjectWithHandler($object, ObjectBasicsHandler $handler)
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

    private function containsObject(ObjectBasics $object)
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
        $this->elementCount--;
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