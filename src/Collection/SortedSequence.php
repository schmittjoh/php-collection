<?php
namespace Collection;

/**
 * A sequence with a fixed sort-order.
 *
 * @author J. M. Schmitt, A. Sukharev
 */
class SortedSequence extends Sequence implements \JsonSerializable
{
    private $sortFunc;

    public function __construct(callable $sortFunc, iterable $elements = [])
    {
        $this->sortFunc = $sortFunc;

        parent::__construct($elements);
    }

    /**
     * {@inheritdoc}
     */
    public function add(mixed $newElement): self
    {
        /** @var callable $sortFunc */
        $sortFunc = $this->sortFunc;

        $added = false;
        $newElements = [];

        foreach ($this->elements as $element) {
            // We insert the new element before the first element that is greater than itself.
            if (!$added && ((int)$sortFunc($newElement, $element) < 0)) {
                $newElements[] = $newElement;
                $added = true;
            }

            $newElements[] = $element;
        }

        if (!$added) {
            $newElements[] = $newElement;
        }

        $this->elements = $newElements;

        return $this;
    }

    public function addAll(iterable $values): self
    {
        $sortFunc = $this->sortFunc;

        $elements = [];
        array_push ($elements, ...$values);
        usort($elements, $sortFunc);

        $newElements = [];
        foreach ($this->elements as $element) {
            if (!empty($elements)) {
                foreach ($elements as $i => $newElement) {
                    // If the currently looked at $newElement is not smaller than $element, then we can also conclude
                    // that all other new elements are also not smaller than $element as we have ordered them before.
                    if ((int)$sortFunc($newElement, $element) > -1) {
                        break;
                    }

                    $newElements[] = $newElement;
                    unset($elements[$i]);
                }
            }

            $newElements[] = $element;
        }

        if (!empty($elements)) {
            foreach ($elements as $newElement) {
                $newElements[] = $newElement;
            }
        }

        $this->elements = $newElements;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function createNew($elements): self
    {
        return new static($this->sortFunc, $elements);
    }
}
