<?php
/*
 * Copyright (C) 2016 Johannes M. Schmitt, Artyom Sukharev
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

/**
 * Unsorted sequence implementation.
 *
 * Characteristics:
 *
 *     - Keys: consequentially numbered, without gaps
 *     - Values: anything, duplicates allowed
 *     - Ordering: same as input unless when explicitly sorted
 *
 * @author Artyom Sukharev , J. M. Schmitt
 */
class Sequence extends AbstractSequence implements SortableInterface
{
    /**
     * @param $callable
     * @return SequenceInterface
     */
    public function sortWith(callable $callable)
    {
        usort($this->elements, $callable);

        return $this;
    }
}
