<?php

namespace Collection;

/**
 * Interface for external handlers that provide ObjectBasics functionality.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
interface ObjectBasicsHandler
{
    /**
     * @param object $object This object is guaranteed to be of the type the handler was registered for.
     * @return string|integer
     */
    public function hash($object);

    /**
     * @param object $firstObject This object is guaranteed to be of the type the handler was registered for.
     * @param object $secondObject This might be an object of any class.
     * @return boolean
     */
    public function equals($firstObject, $secondObject);
}