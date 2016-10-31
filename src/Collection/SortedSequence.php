<?php

/*
 * Copyright 2016 Johannes M. Schmitt, Artyom Sukharev <aly.casus@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Collection;

/**
 * A sequence with a fixed sort-order.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
class SortedSequence extends AbstractSequence
{
    private $sortFunc;

    public function __construct(callable $sortFunc, array $elements = [])
    {
        $this->sortFunc = $sortFunc;

        parent::__construct($elements);
    }

    public function add($newElement)
    {
        $added = false;
        $newElements = [];
        foreach ($this->elements as $element) {
            // We insert the new element before the first element that is greater than itself.
            if (!$added && (integer)call_user_func($this->sortFunc, $newElement, $element) < 0) {
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

    public function addAll($addedElements)
    {
        usort($addedElements, $this->sortFunc);

        $newElements = [];
        foreach ($this->elements as $element) {
            if (!empty($addedElements)) {
                foreach ($addedElements as $i => $newElement) {
                    // If the currently looked at $newElement is not smaller than $element, then we can also conclude
                    // that all other new elements are also not smaller than $element as we have ordered them before.
                    if ((integer)call_user_func($this->sortFunc, $newElement, $element) > -1) {
                        break;
                    }

                    $newElements[] = $newElement;
                    unset($addedElements[$i]);
                }
            }

            $newElements[] = $element;
        }

        if (!empty($addedElements)) {
            foreach ($addedElements as $newElement) {
                $newElements[] = $newElement;
            }
        }

        $this->elements = $newElements;

        return $this;
    }

    protected function createNew($elements)
    {
        return new static($this->sortFunc, $elements);
    }
}