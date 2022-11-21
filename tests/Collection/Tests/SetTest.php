<?php

namespace Collection\Tests;

use Collection\HashableInterface;
use Collection\Set;
use PHPUnit\Framework\TestCase;

final class SetTest extends TestCase
{
    private Set $set;

    public function testHead()
    {
        $this->set->addAll(['foo', 'bar']);
        $this->assertEquals('foo', $this->set->headOption()->get());
        $this->assertEquals('foo', $this->set->head());

        $this->set = new Set();
        $this->assertTrue($this->set->headOption()->isEmpty());
        $this->assertNull($this->set->head());
    }

    public function testLast()
    {
        $this->set->addAll(['baz', 'boo']);
        $this->assertEquals('boo', $this->set->lastOption()->get());
        $this->assertEquals('boo', $this->set->last());

        $this->set = new Set();
        $this->assertTrue($this->set->lastOption()->isEmpty());
        $this->assertNull($this->set->last());
    }

    public function testTail()
    {
        $this->set->addAll(['foo', 'bar', 'baz', 'boo']);
        $this->assertEquals('bar', $this->set->tail()->headOption()->get());
    }

    public function testContainsScalar()
    {
        $this->set->add('a');

        $this->assertFalse($this->set->contains('b'));
        $this->assertFalse($this->set->contains(new HashableObject('foo')));
        $this->assertFalse($this->set->contains(new \DateTime('today')));
    }

    public function testContainsObjectWithHandler()
    {
        $someObjectVal = new \DateTime('today');
        $this->set->add($someObjectVal);

        $this->assertFalse($this->set->contains(new HashableObject('foo')));
        $this->assertFalse($this->set->contains('a'));

        $this->assertTrue($this->set->contains($someObjectVal));
    }

    public function testContainsObject()
    {
        $this->set->add(new HashableObject('foo'));

        $this->assertFalse($this->set->contains(new HashableObject('bar')));
        $this->assertFalse($this->set->contains('a'));
        $this->assertFalse($this->set->contains(new \DateTime()));

        $this->assertTrue($this->set->contains(new HashableObject('foo')));
    }

    public function testReverse()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(['a', 'b'], $this->set->all());

        $reversedSet = $this->set->reverse();
        $this->assertEquals(['a', 'b'], $this->set->all());
        $this->assertEquals(['b', 'a'], $reversedSet->all());
    }

    public function testMap()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(['a', 'b'], $this->set->all());

        $newSet = $this->set->map(function ($char) {
            if ($char === 'a') {
                return 'c';
            } elseif ($char === 'b') {
                return 'd';
            }

            return $char;
        });

        $this->assertEquals(['a', 'b'], $this->set->all());
        $this->assertEquals(['c', 'd'], $newSet->all());
    }

    public function testRemoveScalar()
    {
        $this->set->add('a');
        $this->assertCount(1, $this->set);

        $this->set->remove('b');
        $this->assertCount(1, $this->set);

        $this->set->remove('a');
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testRemoveObjectWithHandler()
    {
        $someObjectVal = new \DateTime('today');

        $this->set->add($someObjectVal);
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('-2 days'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('today'));
        $this->assertCount(1, $this->set);

        $this->set->remove($someObjectVal);
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testRemoveObject()
    {
        $someObjectVal = new HashableObject('foo');

        $this->set->add($someObjectVal);
        $this->assertCount(1, $this->set);

        $this->set->remove(new HashableObject('bar'));
        $this->assertCount(1, $this->set);

        $this->set->remove($someObjectVal);
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testAddScalar()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->set->add('a');

        $this->assertEquals(['a', 'b'], $this->set->all());
    }

    public function testAddObject()
    {
        $this->set->add(new HashableObject('foo'));
        $this->set->add(new HashableObject('bar'));
        $this->set->add(new HashableObject('foo'));

        $this->assertEquals(
            [
                new HashableObject('foo'),
                new HashableObject('bar'),
            ],
            $this->set->all()
        );
    }

    public function testAddObjectWithHandler()
    {
        $date1 = (new DateTimeHashable('today'))->setTimezone(new \DateTimeZone('UTC'));

        $dates = [];
        $dates[] = (new DateTimeHashable('today'))->setTimezone(new \DateTimeZone('UTC'));
        $dates[] = (new DateTimeHashable('-2 days'))->setTimezone(new \DateTimeZone('UTC'));
        $dates[] = (new DateTimeHashable('today'))->setTimezone(new \DateTimeZone('US/Pacific'));

        $this->set->addAll($dates);
        $this->set->add($date1);

        $this->assertEquals(
            $dates,
            $this->set->all()
        );
    }

    protected function setUp(): void
    {
        $this->set = new Set();
    }
}

class HashableObject implements HashableInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function hash(): string
    {
        return sha1($this->value); // This is not recommended in the real-world.
    }
}

class DateTimeHashable extends \DateTime implements HashableInterface
{
    public function hash(): string
    {
        return $this->format("YmdP");
    }
}