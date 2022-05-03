<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
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

namespace PhpCollection;

use PhpOption\LazyOption;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

abstract class AbstractCollection implements \IteratorAggregate
{
    public function contains(mixed $searchedElement): bool
    {
        foreach ($this as $elem) {
            if ($elem === $searchedElement) {
                return true;
            }
        }

        return false;
    }

    public function find(\Closure $callable): Option
    {
        $self = $this;

        return new LazyOption(function () use ($callable, $self) {
            foreach ($self as $elem) {
                if (true === call_user_func($callable, $elem)) {
                    return new Some($elem);
                }
            }

            return None::create();
        });
    }
}
