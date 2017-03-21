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
 * Interface for sortable collections.
 *
 * @author Artyom Sukharev , J. M. Schmitt
 */
interface SortableInterface
{
    public function sortWith(callable $callable);
}
