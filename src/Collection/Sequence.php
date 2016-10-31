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
 * Unsorted sequence implementation.
 *
 * Characteristics:
 *
 *     - Keys: consequentially numbered, without gaps
 *     - Values: anything, duplicates allowed
 *     - Ordering: same as input unless when explicitly sorted
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
class Sequence extends AbstractSequence implements SortableInterface
{
    /**
     * @param $callable
     * @return $this
     */
    public function sortWith(callable $callable)
    {
        usort($this->elements, $callable);

        return $this;
    }
}