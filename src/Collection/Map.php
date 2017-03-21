<?php
namespace Collection;

class Map extends AbstractMap implements SortableInterface, \JsonSerializable
{
    /**
     * @param $callable
     *
     * @return MapInterface
     */
    public function sortWith(callable $callable)
    {
        uksort($this->elements, $callable);

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->all();
    }
}
