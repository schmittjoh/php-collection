<?php

namespace PhpCollection\Tests;

use PhpCollection\SortedSequence;
use PHPUnit\Framework\TestCase;

class SortedSequenceTest extends TestCase
{
    private SortedSequence $seq;
    private \stdClass $a;
    private \stdClass $b;

    protected function setUp(): void
    {
        $this->seq = new SortedSequence(function ($a, $b) {
            if (is_integer($a)) {
                if (!is_integer($b)) {
                    return -1;
                }

                return $a - $b;
            }

            if (is_integer($b)) {
                return 1;
            }

            return -1;
        });
        $this->seq->addAll([
            0,
            $this->a = new \stdClass(),
            $this->b = new \stdClass(),
            0,
        ]);
    }

    public function testAdd(): void
    {
        $this->seq->add(1);
        $this->assertSame([0, 0, 1, $this->a, $this->b], $this->seq->all());

        $this->seq->add(2);
        $this->assertSame([0, 0, 1, 2, $this->a, $this->b], $this->seq->all());
    }

    public function testAddAll(): void
    {
        $this->seq->addAll([2, 1, 3]);
        $this->assertSame([0, 0, 1, 2, 3, $this->a, $this->b], $this->seq->all());

        $this->seq->addAll([2, 3, 1, 2]);
        $this->assertSame([0, 0, 1, 1, 2, 2, 2, 3, 3, $this->a, $this->b], $this->seq->all());
    }

    public function testTake(): void
    {
        $seq = $this->seq->take(2);
        $this->assertInstanceOf(\PhpCollection\SortedSequence::class, $seq);
        $this->assertSame([0, 0], $seq->all());
    }
}
