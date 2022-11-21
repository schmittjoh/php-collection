<?php

namespace Collection\Tests;

use Collection\Map;
use PhpOption\LazyOption;
use PHPUnit\Framework\TestCase;

final class MapTest extends TestCase
{
    /** @var Map */
    protected $map;

    public function testExists()
    {
        $this->assertFalse(
            $this->map->exists(
                function ($k) {
                    return $k === 0;
                }
            )
        );

        $this->map->set('foo', 'bar');
        $this->assertTrue(
            $this->map->exists(
                function ($k, $v) {
                    return $k === 'foo' && $v === 'bar';
                }
            )
        );
    }

    public function testSet()
    {
        $this->assertTrue($this->map->get('asdf')->isEmpty());
        $this->map->set('asdf', 'foo');
        $this->assertEquals('foo', $this->map->get('asdf')->get());

        $this->assertEquals('bar', $this->map->get('foo')->get());
        $this->map->set('foo', 'asdf');
        $this->assertEquals('asdf', $this->map->get('foo')->get());
    }

    public function testSetSetAll()
    {
        $this->map->setAll(['foo' => 'asdf', 'bar' => ['foo']]);
        $this->assertEquals(['foo' => 'asdf', 'bar' => ['foo'], 'baz' => 'boo'], iterator_to_array($this->map));
    }

    public function testAll()
    {
        $this->map->setAll(['foo' => 'asdf', 'bar' => ['foo']]);
        $this->assertEquals(['foo' => 'asdf', 'bar' => ['foo'], 'baz' => 'boo'], $this->map->all());
    }

    public function testAddMap()
    {
        $map = new Map();
        $map->set('foo', ['bar']);
        $this->map->addMap($map);

        $this->assertEquals(['foo' => ['bar'], 'bar' => 'baz', 'baz' => 'boo'], iterator_to_array($this->map));
    }

    public function testRemove()
    {
        $this->assertTrue($this->map->get('foo')->isDefined());
        $this->assertEquals('bar', $this->map->remove('foo'));
        $this->assertFalse($this->map->get('foo')->isDefined());
    }

    public function testClear()
    {
        $this->assertCount(3, $this->map);
        $this->map->clear();
        $this->assertCount(0, $this->map);
    }

    public function testRemoveWithUnknownIndex()
    {
        $this->expectExceptionMessage("The map has no key named \"asdfasdf\".");
        $this->expectException(\InvalidArgumentException::class);
        $this->map->remove('asdfasdf');
    }

    public function testHead()
    {
        $this->assertEquals(['foo', 'bar'], $this->map->headOption()->get());
        $this->assertEquals(['foo', 'bar'], $this->map->head());

        $this->map->clear();
        $this->assertTrue($this->map->headOption()->isEmpty());
        $this->assertNull($this->map->head());
    }

    public function testLast()
    {
        $this->assertEquals(['baz', 'boo'], $this->map->lastOption()->get());
        $this->assertEquals(['baz', 'boo'], $this->map->last());

        $this->map->clear();
        $this->assertTrue($this->map->lastOption()->isEmpty());
        $this->assertNull($this->map->last());
    }

    public function testTail()
    {
        $this->assertEquals(['bar', 'baz'], $this->map->tail()->headOption()->get());
    }

    public function testContains()
    {
        $this->assertTrue($this->map->contains('boo'));
        $this->assertFalse($this->map->contains('asdf'));
    }

    public function testContainsKey()
    {
        $this->assertTrue($this->map->containsKey('foo'));
        $this->assertFalse($this->map->containsKey('boo'));
    }

    public function testIsEmpty()
    {
        $this->assertFalse($this->map->isEmpty());
        $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }

    public function testFilter()
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $newMap = $map->filter(
            function ($k, $v) {
                return $v === 'd';
            }
        );

        $this->assertNotSame($newMap, $map);
        $this->assertCount(3, $map);
        $this->assertCount(1, $newMap);
        $this->assertEquals(['c' => 'd'], iterator_to_array($newMap));
    }

    public function testFilterNot()
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $newMap = $map->filterNot(
            function ($k, $v) {
                return $v === 'd';
            }
        );

        $this->assertNotSame($newMap, $map);
        $this->assertCount(3, $map);
        $this->assertCount(2, $newMap);
        $this->assertEquals(['a' => 'b', 'e' => 'f'], iterator_to_array($newMap));
    }

    public function testFoldLeftRight()
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $rsLeft = $map->foldLeft(
            '',
            function ($a, $k, $b) {
                return $a . $b;
            }
        );
        $rsRight = $map->foldRight(
            '',
            function ($a, $k, $b) {
                return $a . $b;
            }
        );

        $this->assertEquals('bdf', $rsLeft);
        $this->assertEquals('fdb', $rsRight);
    }

    public function testDropWhile()
    {
        $newMap = $this->map->dropWhile(
            function ($k, $v) {
                return 'foo' === $k || 'baz' === $v;
            }
        );
        $this->assertEquals(['baz' => 'boo'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDrop()
    {
        $newMap = $this->map->drop(2);
        $this->assertEquals(['baz' => 'boo'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDropWithNegativeNumber()
    {
        $this->expectExceptionMessage("The number must be greater than 0, but got -4.");
        $this->expectException(\InvalidArgumentException::class);
        $this->map->drop(-4);
    }

    public function testDropRight()
    {
        $newMap = $this->map->dropRight(2);
        $this->assertEquals(['foo' => 'bar'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDropRightWithNegativeNumber()
    {
        $this->expectExceptionMessage("The number must be greater than 0, but got -5.");
        $this->expectException(\InvalidArgumentException::class);
        $this->map->dropRight(-5);
    }

    public function testTake()
    {
        $newMap = $this->map->take(1);
        $this->assertEquals(['foo' => 'bar'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testTakeWithNegativeNumber()
    {
        $this->expectExceptionMessage("The number must be greater than 0, but got -5.");
        $this->expectException(\InvalidArgumentException::class);
        $this->map->take(-5);
    }

    public function testTakeWhile()
    {
        $newMap = $this->map->takeWhile(
            function ($k, $v) {
                return 'foo' === $k || 'baz' === $v;
            }
        );
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testFind()
    {
        /** @var LazyOption $foundElem */
        $foundElem = $this->map->find(
            function ($k, $v) {
                return 'foo' === $k && 'bar' === $v;
            }
        );
        $this->assertEquals(['foo', 'bar'], $foundElem->get());

        $this->assertTrue(
            $this->map->find(
                function () {
                    return false;
                }
            )->isEmpty()
        );
    }

    public function testKeys()
    {
        $this->assertEquals(['foo', 'bar', 'baz'], $this->map->keys());
    }

    public function testValues()
    {
        $this->assertEquals(['bar', 'baz', 'boo'], $this->map->values());
    }

    protected function setUp(): void
    {
        $this->map = new Map();
        $this->map->setAll(
            [
                'foo' => 'bar',
                'bar' => 'baz',
                'baz' => 'boo',
            ]
        );
    }
}
