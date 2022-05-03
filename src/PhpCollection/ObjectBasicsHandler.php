<?php

namespace PhpCollection;

/**
 * Interface for external handlers that provide ObjectBasics functionality.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ObjectBasicsHandler
{
    /**
     * @param object $object this object is guaranteed to be of the type the handler was registered for
     */
    public function hash($object): string|int;

    /**
     * @param object $firstObject this object is guaranteed to be of the type the handler was registered for
     * @param object $secondObject this might be an object of any class
     * @return bool
     */
    public function equals($firstObject, $secondObject): bool;
}
