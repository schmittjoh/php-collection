<?php
declare(strict_types=1);

namespace Collection;

interface HashableInterface
{
    public function hash(): string;
}