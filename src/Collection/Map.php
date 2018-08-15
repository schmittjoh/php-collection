<?php
namespace Collection;

class Map extends AbstractMap implements MapInterface, SortableInterface, \JsonSerializable
{
    /**
     * @param $callable
     *
     * @return $this|MapInterface
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
