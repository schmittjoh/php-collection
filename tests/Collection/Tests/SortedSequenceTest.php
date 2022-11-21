<?php

namespace Collection\Tests;

use Collection\SortedSequence;
use PHPUnit\Framework\TestCase;

final class SortedSequenceTest extends TestCase
{
    /** @var SortedSequence */
    private $seq;
    private $a;
    private $b;

    public function testAdd()
    {
        $this->seq->add(1);
        $this->assertSame([0, 0, 1, $this->a, $this->b], $this->seq->all());

        $this->seq->add(2);
        $this->assertSame([0, 0, 1, 2, $this->a, $this->b], $this->seq->all());
    }

    public function testAddAll()
    {
        $this->seq->addAll([2, 1, 3]);
        $this->assertSame([0, 0, 1, 2, 3, $this->a, $this->b], $this->seq->all());

        $this->seq->addAll([2, 3, 1, 2]);
        $this->assertSame([0, 0, 1, 1, 2, 2, 2, 3, 3, $this->a, $this->b], $this->seq->all());
    }

    public function testTake()
    {
        $seq = $this->seq->take(2);
        $this->assertInstanceOf('Collection\SortedSequence', $seq);
        $this->assertSame([0, 0], $seq->all());
    }

    protected function setUp(): void
    {
        $this->seq = new SortedSequence(
            function ($a, $b) {
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
            }
        );

        $this->seq->addAll(
            [
                0,
                $this->a = new \stdClass,
                $this->b = new \stdClass,
                0,
            ]
        );
    }
}