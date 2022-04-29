<?php

namespace PhpCollection\Tests;

use PhpCollection\Map;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    private Map $map;

    protected function setUp(): void
    {
        $this->map = new Map();
        $this->map->setAll([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'boo',
        ]);
    }

    public function testExists(): void
    {
        $this->assertFalse($this->map->exists(fn ($k) => 0 === $k));

        $this->map->set('foo', 'bar');
        $this->assertTrue($this->map->exists(fn ($k, $v) => 'foo' === $k && 'bar' === $v));
    }

    public function testSet(): void
    {
        $this->assertTrue($this->map->get('asdf')->isEmpty());
        $this->map->set('asdf', 'foo');
        $this->assertEquals('foo', $this->map->get('asdf')->get());

        $this->assertEquals('bar', $this->map->get('foo')->get());
        $this->map->set('foo', 'asdf');
        $this->assertEquals('asdf', $this->map->get('foo')->get());
    }

    public function testSetSetAll(): void
    {
        $this->map->setAll(['foo' => 'asdf', 'bar' => ['foo']]);
        $this->assertEquals(['foo' => 'asdf', 'bar' => ['foo'], 'baz' => 'boo'], iterator_to_array($this->map));
    }

    public function testAll(): void
    {
        $this->map->setAll(['foo' => 'asdf', 'bar' => ['foo']]);
        $this->assertEquals(['foo' => 'asdf', 'bar' => ['foo'], 'baz' => 'boo'], $this->map->all());
    }

    public function testAddMap(): void
    {
        $map = new Map();
        $map->set('foo', ['bar']);
        $this->map->addMap($map);

        $this->assertEquals(['foo' => ['bar'], 'bar' => 'baz', 'baz' => 'boo'], iterator_to_array($this->map));
    }

    public function testRemove(): void
    {
        $this->assertTrue($this->map->get('foo')->isDefined());
        $this->assertEquals('bar', $this->map->remove('foo'));
        $this->assertFalse($this->map->get('foo')->isDefined());
    }

    public function testClear(): void
    {
        $this->assertCount(3, $this->map);
        $this->map->clear();
        $this->assertCount(0, $this->map);
    }

    public function testRemoveWithUnknownIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The map has no key named "asdfasdf".');
        $this->map->remove('asdfasdf');
    }

    public function testFirst(): void
    {
        $this->assertEquals(['foo', 'bar'], $this->map->first()->get());
        $this->map->clear();
        $this->assertTrue($this->map->first()->isEmpty());
    }

    public function testLast(): void
    {
        $this->assertEquals(['baz', 'boo'], $this->map->last()->get());
        $this->map->clear();
        $this->assertTrue($this->map->last()->isEmpty());
    }

    public function testContains(): void
    {
        $this->assertTrue($this->map->contains('boo'));
        $this->assertFalse($this->map->contains('asdf'));
    }

    public function testContainsKey(): void
    {
        $this->assertTrue($this->map->containsKey('foo'));
        $this->assertFalse($this->map->containsKey('boo'));
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse($this->map->isEmpty());
        $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }

    public function testFilter(): void
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $newMap = $map->filter(fn ($v) => 'd' === $v);

        $this->assertNotSame($newMap, $map);
        $this->assertCount(3, $map);
        $this->assertCount(1, $newMap);
        $this->assertEquals(['c' => 'd'], iterator_to_array($newMap));
    }

    public function testFilterNot(): void
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $newMap = $map->filterNot(fn ($v): bool => 'd' === $v);

        $this->assertNotSame($newMap, $map);
        $this->assertCount(3, $map);
        $this->assertCount(2, $newMap);
        $this->assertEquals(['a' => 'b', 'e' => 'f'], iterator_to_array($newMap));
    }

    public function testFoldLeftRight(): void
    {
        $map = new Map(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $rsLeft = $map->foldLeft('', fn ($a, $b) => $a.$b);
        $rsRight = $map->foldRight('', fn ($a, $b) => $a.$b);

        $this->assertEquals('bdf', $rsLeft);
        $this->assertEquals('bdf', $rsRight);
    }

    public function testDropWhile(): void
    {
        $newMap = $this->map->dropWhile(fn ($k, $v) => 'foo' === $k || 'baz' === $v);
        $this->assertEquals(['baz' => 'boo'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDrop(): void
    {
        $newMap = $this->map->drop(2);
        $this->assertEquals(['baz' => 'boo'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDropWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The number must be greater than 0, but got -4.');
        $this->map->drop(-4);
    }

    public function testDropRight(): void
    {
        $newMap = $this->map->dropRight(2);
        $this->assertEquals(['foo' => 'bar'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testDropRightWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The number must be greater than 0, but got -5.');
        $this->map->dropRight(-5);
    }

    public function testTake(): void
    {
        $newMap = $this->map->take(1);
        $this->assertEquals(['foo' => 'bar'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testTakeWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The number must be greater than 0, but got -5.');

        $this->map->take(-5);
    }

    public function testTakeWhile(): void
    {
        $newMap = $this->map->takeWhile(fn ($k, $v) => 'foo' === $k || 'baz' === $v);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], iterator_to_array($newMap));
        $this->assertCount(3, $this->map);
    }

    public function testFind(): void
    {
        $foundElem = $this->map->find(fn ($k, $v) => 'foo' === $k && 'bar' === $v);
        $this->assertEquals(['foo', 'bar'], $foundElem->get());

        $this->assertTrue($this->map->find(fn () => false)->isEmpty());
    }

    public function testKeys(): void
    {
        $this->assertEquals(['foo', 'bar', 'baz'], $this->map->keys());
    }

    public function testValues(): void
    {
        $this->assertEquals(['bar', 'baz', 'boo'], $this->map->values());
    }
}
