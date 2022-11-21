<?php
namespace Collection;

/**
 * Interface for sortable collections.
 *
 * @author J. M. Schmitt, A. Sukharev
 */
interface SortableInterface
{
    public function sortWith(callable $callable);
}
