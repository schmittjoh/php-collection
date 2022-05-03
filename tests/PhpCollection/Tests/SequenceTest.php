<?php

namespace PhpCollection\Tests;

use PhpCollection\Sequence;
use PHPUnit\Framework\TestCase;
use stdClass;

class SequenceTest extends TestCase
{
    private Sequence $seq;
    private \stdClass $a;
    private \stdClass $b;

    protected function setUp(): void
    {
        $this->seq = new Sequence();
        $this->seq->addAll([
            0,
            $this->a = new \stdClass(),
            $this->b = new \stdClass(),
            0,
        ]);
    }

    public function testGet(): void
    {
        $this->assertSame(0, $this->seq->get(0));
        $this->assertSame($this->a, $this->seq->get(1));
    }

    public function testIndexOf(): void
    {
        $this->assertSame(0, $this->seq->indexOf(0));
        $this->assertSame(1, $this->seq->indexOf($this->a));
        $this->assertSame(2, $this->seq->indexOf($this->b));
        $this->assertSame(-1, $this->seq->indexOf(1));
    }

    public function testReverse(): void
    {
        $seq = new Sequence([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $seq->all());
        $this->assertEquals([3, 2, 1], $seq->reverse()->all());
    }

    public function testLastIndexOf(): void
    {
        $this->assertSame(3, $this->seq->lastIndexOf(0));
        $this->assertSame(1, $this->seq->lastIndexOf($this->a));
        $this->assertSame(2, $this->seq->lastIndexOf($this->b));
        $this->assertSame(-1, $this->seq->lastIndexOf(1));
    }

    public function testFilter(): void
    {
        $seq = new Sequence([1, 2, 3]);
        $newSeq = $seq->filter(fn ($n) => 2 === $n);

        $this->assertNotSame($newSeq, $seq);
        $this->assertCount(3, $seq);
        $this->assertCount(1, $newSeq);
        $this->assertSame(2, $newSeq->get(0));
    }

    public function testFilterNot(): void
    {
        $seq = new Sequence([1, 2, 3]);
        $newSeq = $seq->filterNot(fn ($n) => 2 === $n);

        $this->assertNotSame($newSeq, $seq);
        $this->assertCount(3, $seq);
        $this->assertCount(2, $newSeq);
        $this->assertSame(1, $newSeq->get(0));
        $this->assertSame(3, $newSeq->get(1));
    }

    public function testFoldLeftRight(): void
    {
        $seq = new Sequence(['a', 'b', 'c']);
        $rsLeft = $seq->foldLeft('', fn ($a, $b) => $a.$b);
        $rsRight = $seq->foldRight('', fn ($a, $b) => $a.$b);

        $this->assertEquals('abc', $rsLeft);
        $this->assertEquals('abc', $rsRight);
    }

    public function testAddSequence(): void
    {
        $seq = new Sequence();
        $seq->add(1);
        $seq->add(0);

        $this->seq->addSequence($seq);

        $this->assertSame([
            0,
            $this->a,
            $this->b,
            0,
            1,
            0,
        ], $this->seq->all());
    }

    public function testIsDefinedAt(): void
    {
        $this->assertTrue($this->seq->isDefinedAt(0));
        $this->assertTrue($this->seq->isDefinedAt(1));
        $this->assertFalse($this->seq->isDefinedAt(9_999_999));
    }

    public function testIndexWhere(): void
    {
        $this->assertSame(-1, $this->seq->indexWhere(fn () => false));
        $this->assertSame(0, $this->seq->indexWhere(fn () => true));
    }

    public function testLastIndexWhere(): void
    {
        $this->assertSame(-1, $this->seq->lastIndexWhere(fn () => false));
        $this->assertSame(3, $this->seq->lastIndexWhere(fn () => true));
    }

    public function testFirst(): void
    {
        $this->assertSame(0, $this->seq->first()->get());
        $this->assertSame(0, $this->seq->last()->get());
    }

    public function testIndices(): void
    {
        $this->assertSame([0, 1, 2, 3], $this->seq->indices());
    }

    public function testContains(): void
    {
        $this->assertTrue($this->seq->contains(0));
        $this->assertTrue($this->seq->contains($this->a));
        $this->assertFalse($this->seq->contains(9999));
        $this->assertFalse($this->seq->contains(new stdClass()));
    }

    public function testExists(): void
    {
        $this->assertTrue($this->seq->exists(fn ($v) => 0 === $v));

        $a = $this->a;
        $this->assertTrue($this->seq->exists(fn ($v) => $v === $a));

        $this->assertFalse($this->seq->exists(fn ($v) => 9999 === $v));
        $this->assertFalse($this->seq->exists(fn ($v) => $v === new \stdClass()));
    }

    public function testFind(): void
    {
        $a = $this->a;

        $this->assertSame($this->a, $this->seq->find(fn ($x) => $a === $x)->get());
        $this->assertFalse($this->seq->find(fn () => false)->isDefined());
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse($this->seq->isEmpty());
        $seq = new Sequence();
        $this->assertTrue($seq->isEmpty());
    }

    public function testAdd(): void
    {
        $this->seq->add(1);
        $this->assertSame([0, $this->a, $this->b, 0, 1], $this->seq->all());

        $this->seq->sortWith(function ($a, $b) {
            if (is_integer($a)) {
                if (!is_integer($b)) {
                    return -1;
                }

                return $a > $b ? 1 : -1;
            }

            if (is_integer($b)) {
                return 1;
            }

            if ($a === $this->a && $b === $this->b) {
                return -1;
            } elseif ($a === $this->b && $b === $this->a) {
                return 1;
            }

            return 1;
        });

        $this->assertSame([0, 0, 1, $this->a, $this->b], $this->seq->all());
    }

    public function testUpdate(): void
    {
        $this->assertSame(0, $this->seq->get(0));
        $this->seq->update(0, 5);
        $this->assertSame(5, $this->seq->get(0));
    }

    public function testUpdateWithNonExistentIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no element at index "99999".');
        $this->seq->update(99999, 0);
    }

    public function testAddAll(): void
    {
        $this->seq->addAll([2, 1, 3]);
        $this->assertSame([0, $this->a, $this->b, 0, 2, 1, 3], $this->seq->all());

        $this->seq->sortWith(function ($a, $b) {
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
        });

        $this->assertSame([0, 0, 1, 2, 3, $this->a, $this->b], $this->seq->all());
    }

    public function testTake(): void
    {
        $this->assertSame([0], $this->seq->take(1)->all());
        $this->assertSame([0, $this->a], $this->seq->take(2)->all());
        $this->assertSame([0, $this->a, $this->b, 0], $this->seq->take(9999)->all());
    }

    public function testTakeWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$number must be greater than 0, but got -5.');
        $this->seq->take(-5);
    }

    public function testTakeWhile(): void
    {
        $this->assertSame([0], $this->seq->takeWhile('is_integer')->all());
    }

    public function testCount(): void
    {
        $this->assertCount(4, $this->seq);
    }

    public function testTraverse(): void
    {
        $this->assertSame([0, $this->a, $this->b, 0], iterator_to_array($this->seq));
    }

    public function testDrop(): void
    {
        $this->assertSame([$this->a, $this->b, 0], $this->seq->drop(1)->all());
        $this->assertSame([$this->b, 0], $this->seq->drop(2)->all());
        $this->assertSame([], $this->seq->drop(9999)->all());
    }

    public function testDropWithNegativeIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The number must be greater than 0, but got -5.');
        $this->seq->drop(-5);
    }

    public function testDropRight(): void
    {
        $this->assertSame([0, $this->a, $this->b], $this->seq->dropRight(1)->all());
        $this->assertSame([0, $this->a], $this->seq->dropRight(2)->all());
        $this->assertSame([], $this->seq->dropRight(9999)->all());
    }

    public function testDropRightWithNegativeIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The number must be greater than 0, but got -5.');
        $this->seq->dropRight(-5);
    }

    public function testDropWhile(): void
    {
        $this->assertSame([0, $this->a, $this->b, 0], $this->seq->dropWhile(fn () => false)->all());
        $this->assertSame([], $this->seq->dropWhile(fn () => true)->all());
    }

    public function testRemove(): void
    {
        $this->assertSame(0, $this->seq->remove(0));
        $this->assertSame($this->a, $this->seq->remove(0));
        $this->assertSame(0, $this->seq->remove(1));
    }

    public function testRemoveWithInvalidIndex(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The index "9999" is not in the interval [0, 4).');
        $this->seq->remove(9999);
    }

    public function testMap(): void
    {
        $seq = new Sequence();
        $seq->add('a');
        $seq->add('b');

        $self = $this;
        $newSeq = $seq->map(fn ($elem) => match ($elem) {
            'a' => 'c',
            'b' => 'd',
            default => $self->fail('Unexpected element: '.var_export($elem, true)),
        });

        $this->assertInstanceOf(\PhpCollection\Sequence::class, $newSeq);
        $this->assertNotSame($newSeq, $seq);
        $this->assertEquals(['c', 'd'], $newSeq->all());
    }

    public function testIterator(): void
    {
        $seq = new Sequence([1, 2, 3]);
        $this->assertIsIterable($seq);
        $i = 1;
        foreach ($seq as $elem) {
            $this->assertSame($i, $elem);
            $i++;
        }
        $this->assertSame(4, $i);
    }
}
