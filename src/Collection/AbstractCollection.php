<?php
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
