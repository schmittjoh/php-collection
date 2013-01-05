<?php

namespace PhpCollection\Tests;

use PhpCollection\Sequence;
use OutOfBoundsException;
use stdClass;

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    private $seq;
    private $a;
    private $b;

    public function testGet()
    {
        $this->assertSame(0, $this->seq->get(0));
        $this->assertSame($this->a, $this->seq->get(1));
    }

    public function testIndexOf()
    {
        $this->assertSame(0, $this->seq->indexOf(0));
        $this->assertSame(1, $this->seq->indexOf($this->a));
        $this->assertSame(2, $this->seq->indexOf($this->b));
        $this->assertSame(-1, $this->seq->indexOf(1));
    }

    public function testLastIndexOf()
    {
        $this->assertSame(3, $this->seq->lastIndexOf(0));
        $this->assertSame(1, $this->seq->lastIndexOf($this->a));
        $this->assertSame(2, $this->seq->lastIndexOf($this->b));
        $this->assertSame(-1, $this->seq->lastIndexOf(1));
    }

    public function testAddSequence()
    {
        $seq = new Sequence();
        $seq->add(1);
        $seq->add(0);

        $this->seq->addSequence($seq);

        $this->assertSame(array(
            0,
            $this->a,
            $this->b,
            0,
            1,
            0,
        ), $this->seq->all());
    }

    public function testIsDefinedAt()
    {
        $this->assertTrue($this->seq->isDefinedAt(0));
        $this->assertTrue($this->seq->isDefinedAt(1));
        $this->assertFalse($this->seq->isDefinedAt(9999999));
    }

    public function testIndexWhere()
    {
        $this->assertSame(-1, $this->seq->indexWhere(function() { return false; }));
        $this->assertSame(0, $this->seq->indexWhere(function() { return true; }));
    }

    public function testLastIndexWhere()
    {
        $this->assertSame(-1, $this->seq->lastIndexWhere(function() { return false; }));
        $this->assertSame(3, $this->seq->lastIndexWhere(function() { return true; }));
    }

    public function testFirst()
    {
        $this->assertSame(0, $this->seq->first()->get());
        $this->assertSame(0, $this->seq->last()->get());
    }

    public function testIndices()
    {
        $this->assertSame(array(0, 1, 2, 3), $this->seq->indices());
    }

    public function testContains()
    {
        $this->assertTrue($this->seq->contains(0));
        $this->assertTrue($this->seq->contains($this->a));
        $this->assertFalse($this->seq->contains(9999));
        $this->assertFalse($this->seq->contains(new stdClass()));
    }

    public function testFind()
    {
        $a = $this->a;

        $this->assertSame($this->a, $this->seq->find(function($x) use ($a) { return $a === $x; })->get());
        $this->assertFalse($this->seq->find(function() { return false; })->isDefined());
    }

    public function testIsEmpty()
    {
        $this->assertFalse($this->seq->isEmpty());
        $seq = new Sequence();
        $this->assertTrue($seq->isEmpty());
    }

    public function testAdd()
    {
        $this->seq->add(1);
        $this->assertSame(array(0, $this->a, $this->b, 0, 1), $this->seq->all());

        $this->seq->sortWith(function($a, $b) {
            if (is_integer($a)) {
                if ( ! is_integer($b)) {
                    return -1;
                }

                return $a > $b ? 1 : -1;
            }

            if (is_integer($b)) {
                return 1;
            }

            return 1;
        });

        $this->assertSame(array(0, 0, 1, $this->a, $this->b), $this->seq->all());
    }

    public function testUpdate()
    {
        $this->assertSame(0, $this->seq->get(0));
        $this->seq->update(0, 5);
        $this->assertSame(5, $this->seq->get(0));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no element at index "99999".
     */
    public function testUpdateWithNonExistentIndex()
    {
        $this->seq->update(99999, 0);
    }

    public function testAddAll()
    {
        $this->seq->addAll(array(2, 1, 3));
        $this->assertSame(array(0, $this->a, $this->b, 0, 2, 1, 3), $this->seq->all());

        $this->seq->sortWith(function($a, $b) {
            if (is_integer($a)) {
                if ( ! is_integer($b)) {
                    return -1;
                }

                return $a > $b ? 1 : -1;
            }

            if (is_integer($b)) {
                return 1;
            }

            return -1;
        });

        $this->assertSame(array(0, 0, 1, 2, 3, $this->a, $this->b), $this->seq->all());
    }

    public function testTake()
    {
        $this->assertSame(array(0), $this->seq->take(1)->all());
        $this->assertSame(array(0, $this->a), $this->seq->take(2)->all());
        $this->assertSame(array(0, $this->a, $this->b, 0), $this->seq->take(9999)->all());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $number must be greater than 0, but got -5.
     */
    public function testTakeWithNegativeNumber()
    {
        $this->seq->take(-5);
    }

    public function testTakeWhile()
    {
        $this->assertSame(array(0), $this->seq->takeWhile('is_integer')->all());
    }

    public function testCount()
    {
        $this->assertCount(4, $this->seq);
    }

    public function testTraverse()
    {
        $this->assertSame(array(0, $this->a, $this->b, 0), iterator_to_array($this->seq));
    }

    public function testDrop()
    {
        $this->assertSame(array($this->a, $this->b, 0), $this->seq->drop(1)->all());
        $this->assertSame(array($this->b, 0), $this->seq->drop(2)->all());
        $this->assertSame(array(), $this->seq->drop(9999)->all());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The number must be greater than 0, but got -5.
     */
    public function testDropWithNegativeIndex()
    {
        $this->seq->drop(-5);
    }

    public function testDropRight()
    {
        $this->assertSame(array(0, $this->a, $this->b), $this->seq->dropRight(1)->all());
        $this->assertSame(array(0, $this->a), $this->seq->dropRight(2)->all());
        $this->assertSame(array(), $this->seq->dropRight(9999)->all());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The number must be greater than 0, but got -5.
     */
    public function testDropRightWithNegativeIndex()
    {
        $this->seq->dropRight(-5);
    }

    public function testDropWhile()
    {
        $this->assertSame(array(0, $this->a, $this->b, 0), $this->seq->dropWhile(function() { return false; })->all());
        $this->assertSame(array(), $this->seq->dropWhile(function() { return true; })->all());
    }

    public function testRemove()
    {
        $this->assertSame(0, $this->seq->remove(0));
        $this->assertSame($this->a, $this->seq->remove(0));
        $this->assertSame(0, $this->seq->remove(1));
    }

    /**
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage The index "9999" is not in the interval [0, 4).
     */
    public function testRemoveWithInvalidIndex()
    {
        $this->seq->remove(9999);
    }

    protected function setUp()
    {
        $this->seq = new Sequence();
        $this->seq->addAll(array(
            0,
            $this->a = new \stdClass(),
            $this->b = new \stdClass(),
            0
        ));
    }
}