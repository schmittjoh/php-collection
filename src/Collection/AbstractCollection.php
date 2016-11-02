<?php
/*
 * Copyright (C) 2016 Johannes M. Schmitt, Artyom Sukharev <aly.casus@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation, version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collection;

use PhpOption\LazyOption;
use PhpOption\Some;
use PhpOption\None;

abstract class AbstractCollection
{
    public function contains($searchedElem)
    {
        foreach ($this as $elem) {
            if ($elem === $searchedElem) {
                return true;
            }
        }

        return false;
    }

    public function find(callable $callable)
    {
        $self = $this;

        return new LazyOption(
            function () use ($callable, $self) {
                foreach ($self as $elem) {
                    if (call_user_func($callable, $elem) === true) {
                        return new Some($elem);
                    }
                }

                return None::create();
            }
        );
    }
}
