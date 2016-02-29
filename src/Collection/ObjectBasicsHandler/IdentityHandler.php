<?php

namespace Collection\ObjectBasicsHandler;

use Collection\ObjectBasicsHandler;

class IdentityHandler implements ObjectBasicsHandler
{
    public function hash($object)
    {
        return spl_object_hash($object);
    }

    public function equals($a, $b)
    {
        return $a === $b;
    }
}