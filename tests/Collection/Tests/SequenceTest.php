<?php

namespace Collection\Tests;

use Collection\Sequence;
use OutOfBoundsException;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SequenceTest extends TestCase
{
    /** @var Sequence */
    private $seq;
    private $a;
    private $b;

    public function testGet()
    {
        $this->assertSame(0, $this->seq->get(0)->get());
        $this->assertSame($this->a, $this->seq->get(1)->get());
    }

    public function testIndexOf()
    {
        $this->assertSame(0, $this->seq->indexOf(0));
        $this->assertSame(1, $this->seq->indexOf($this->a));
        $this->assertSame(2, $this->seq->indexOf($this->b));
        $this->assertSame(-1, $this->seq->indexOf(1));
    }

    public function testReverse()
    {
        $seq = new Sequence([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $seq->all());
        $this->assertEquals([3, 2, 1], $seq->reverse()->all());
    }

    public function testLastIndexOf()
    {
        $this->assertSame(3, $this->seq->lastIndexOf(0));
        $this->assertSame(1, $this->seq->lastIndexOf($this->a));
        $this->assertSame(2, $this->seq->lastIndexOf($this->b));
        $this->assertSame(-1, $this->seq->lastIndexOf(1));
    }

    public function testFilter()
    {
        $seq = new Sequence([1, 2, 3]);
        $newSeq = $seq->filter(
            function ($n) {
                return $n === 2;
            }
        );

        $this->assertNotSame($newSeq, $seq);
        $this->assertCount(3, $seq);
        $this->assertCount(1, $newSeq);
        $this->assertSame(2, $newSeq->get(0)->get());
    }

    public function testFilterNot()
    {
        $seq = new Sequence([1, 2, 3]);
        $newSeq = $seq->filterNot(
            function ($n) {
                return $n === 2;
            }
        );

        $this->assertNotSame($newSeq, $seq);
        $this->assertCount(3, $seq);
        $this->assertCount(2, $newSeq);
        $this->assertSame(1, $newSeq->get(0)->get());
        $this->assertSame(3, $newSeq->get(1)->get());
    }

    public function testFoldLeftRight()
    {
        $seq = new Sequence(['a', 'b', 'c', 'd']);

        $rsLeft = $seq->foldLeft('', fn($s, $v) => ($s . $v));
        $this->assertEquals('abcd', $rsLeft);

        $rsRight = $seq->foldRight('', fn($s, $v) => ($s . $v));
        $this->assertEquals('dcba', $rsRight);
    }

    public function testAddSequence()
    {
        $seq = new Sequence();
        $seq->add(1);
        $seq->add(0);

        $this->seq->addSequence($seq);

        $this->assertSame(
            [
                0,
                $this->a,
                $this->b,
                0,
                1,
                0,
            ],
            $this->seq->all()
        );
    }

    public function testIsDefinedAt()
    {
        $this->assertTrue($this->seq->isDefinedAt(0));
        $this->assertTrue($this->seq->isDefinedAt(1));
        $this->assertFalse($this->seq->isDefinedAt(9999999));
    }

    public function testIndexWhere()
    {
        $this->assertSame(-1, $this->seq->indexWhere(fn() => false));
        $this->assertSame(0, $this->seq->indexWhere(fn() => true));
    }

    public function testLastIndexWhere()
    {
        $this->assertSame(-1, $this->seq->lastIndexWhere(fn() => false));
        $this->assertSame(3, $this->seq->lastIndexWhere(fn() => true));
    }

    public function testHead()
    {
        $this->assertSame(0, $this->seq->headOption()->get());
        $this->assertSame(0, $this->seq->head());
        $this->assertSame(0, $this->seq->lastOption()->get());
        $this->assertSame(0, $this->seq->last());
    }

    public function testTail()
    {
        $this->assertEquals(new \stdClass(), $this->seq->tail()->headOption()->get());
    }

    public function testIndices()
    {
        $this->assertSame([0, 1, 2, 3], $this->seq->indices());
    }

    public function testContains()
    {
        $this->assertTrue($this->seq->contains(0));
        $this->assertTrue($this->seq->contains($this->a));
        $this->assertFalse($this->seq->contains(9999));
        $this->assertFalse($this->seq->contains(new stdClass()));
    }

    public function testExists()
    {
        $this->assertTrue($this->seq->exists(fn($v) => ($v === 0)));

        $a = $this->a;
        $this->assertTrue($this->seq->exists(fn($v) => ($v === $a)));
        $this->assertFalse($this->seq->exists(fn($v) => ($v === 9999)));
        $this->assertFalse($this->seq->exists(fn($v) => ($v === new \stdClass)));
    }

    public function testFind()
    {
        $a = $this->a;

        $this->assertSame(
            $this->a,
            $this->seq->find(fn($x) => ($a === $x))->get()
        );

        $this->assertFalse(
            $this->seq->find(fn() => false)->isDefined()
        );
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
        $this->assertSame([0, $this->a, $this->b, 0, 1], $this->seq->all());

        $this->seq->sortWith(
            function ($a, $b) {
                if (is_integer($a)) {
                    if (!is_integer($b)) {
                        return -1;
                    }

                    return $a > $b ? 1 : -1;
                }

                if (is_integer($b)) {
                    return 1;
                }

                return 1;
            }
        );

        $this->assertEquals([0, 0, 1, $this->a, $this->b], $this->seq->all());
    }

    public function testUpdate()
    {
        $this->assertSame(0, $this->seq->get(0)->get());
        $this->seq->update(0, 5);
        $this->assertSame(5, $this->seq->get(0)->get());
    }

    public function testUpdateWithNonExistentIndex()
    {
        $this->expectExceptionMessage("no element at index \"99999\".");
        $this->expectException(\OutOfBoundsException::class);
        $this->seq->update(99999, 0);
    }

    public function testAddAll()
    {
        $this->seq->addAll([2, 1, 3]);
        $this->assertSame([0, $this->a, $this->b, 0, 2, 1, 3], $this->seq->all());

        $this->seq->sortWith(
            function ($a, $b) {
                if (is_integer($a)) {
                    if (!is_integer($b)) {
                        return -1;
                    }

                    return $a > $b ? 1 : -1;
                }

                if (is_integer($b)) {
                    return 1;
                }

                return -1;
            }
        );

        $this->assertSame([0, 0, 1, 2, 3, $this->a, $this->b], $this->seq->all());
    }

    public function testTake()
    {
        $this->assertSame([0], $this->seq->take(1)->all());
        $this->assertSame([0, $this->a], $this->seq->take(2)->all());
        $this->assertSame([0, $this->a, $this->b, 0], $this->seq->take(9999)->all());
    }

    public function testTakeWithNegativeNumber()
    {
        $number = -5;
        $this->expectExceptionMessage("\$number must be greater than 0, but got $number.");
        $this->expectException(\InvalidArgumentException::class);
        $this->seq->take($number);
    }

    public function testTakeWhile()
    {
        $this->assertSame([0], $this->seq->takeWhile('is_integer')->all());
    }

    public function testCount()
    {
        $this->assertCount(4, $this->seq);
    }

    public function testTraverse()
    {
        $this->assertSame([0, $this->a, $this->b, 0], iterator_to_array($this->seq));
    }

    public function testDrop()
    {
        $this->assertSame([$this->a, $this->b, 0], $this->seq->drop(1)->all());
        $this->assertSame([$this->b, 0], $this->seq->drop(2)->all());
        $this->assertSame([], $this->seq->drop(9999)->all());
    }

    public function testDropWithNegativeIndex()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The number must be greater than 0, but got -5.");
        $this->seq->drop(-5);
    }

    public function testDropRight()
    {
        $this->assertSame([0, $this->a, $this->b], $this->seq->dropRight(1)->all());
        $this->assertSame([0, $this->a], $this->seq->dropRight(2)->all());
        $this->assertSame([], $this->seq->dropRight(9999)->all());
    }

    public function testDropRightWithNegativeIndex()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The number must be greater than 0, but got -5.");
        $this->seq->dropRight(-5);
    }

    public function testDropWhile()
    {
        $this->assertSame(
            [0, $this->a, $this->b, 0],
            $this->seq->dropWhile(fn() => false)->all()
        );

        $this->assertSame(
            [],
            $this->seq->dropWhile(fn() => true)->all()
        );
    }

    public function testRemove()
    {
        $this->assertSame(0, $this->seq->remove(0)->get());
        $this->assertSame($this->a, $this->seq->remove(0)->get());
        $this->assertSame(0, $this->seq->remove(1)->get());
    }

    public function testRemoveWithInvalidIndex()
    {
        $this->expectException(OutOfBoundsException::class);

        $this->seq
            ->remove(9999)
            ->getOrThrow(new \OutOfBoundsException)
        ;
    }

    public function testMap()
    {
        $seq = new Sequence();
        $seq->add('a');
        $seq->add('b');

        $self = $this;
        $newSeq = $seq->map(
            function ($elem) use ($self) {
                switch ($elem) {
                    case 'a':
                        return 'c';
                    case 'b':
                        return 'd';
                    default:
                        $self->fail('Unexpected element: ' . var_export($elem, true));
                }
            }
        );

        $this->assertInstanceOf('Collection\Sequence', $newSeq);
        $this->assertNotSame($newSeq, $seq);
        $this->assertEquals(['c', 'd'], $newSeq->all());
    }

    public function testFlatMap()
    {
        $seq = new Sequence();
        $seq->addAll([new Some('a'), new Some('b'), None::create(), new Some('c')]);

        $this->assertEquals(
            ['aA', 'bA', 'cA'],
            iterator_to_array(
                $seq->flatMap(fn($x) => $x->map(fn($v) => ($v . 'A')))
            )
        );

        $seq = new Sequence();
        $seq->addAll([[1, 2, 3, 4], [5, 6], [7]]);

        $this->assertEquals(
            [2, 3, 4, 5, 6, 7, 8],
            iterator_to_array(
                $seq->flatMap(fn($x) => array_map(fn($v) => ($v + 1), $x)) // identity
            )
        );

    }

    public function testFlatten()
    {
        $seq = new Sequence();
        $seq->addAll([new Some('a'), new Some('b'), None::create(), new Some('c')]);

        $this->assertEquals(
            ['a', 'b', 'c'],
            iterator_to_array(
                $seq->flatten()
            )
        );

        $seq = new Sequence();
        $seq->addAll([[1, 2, 3, 4], [5, 6], [7]]);

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], iterator_to_array($seq->flatten()));
    }

    protected function setUp(): void
    {
        $this->seq = new Sequence();
        $this->seq->addAll(
            [
                0,
                $this->a = new \stdClass(),
                $this->b = new \stdClass(),
                0,
            ]
        );
    }
}