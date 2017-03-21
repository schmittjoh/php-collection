<?php
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
