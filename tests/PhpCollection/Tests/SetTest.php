<?php

namespace PhpCollection\Tests;

use PhpCollection\ObjectBasics;
use PhpCollection\Set;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    /** @var Set */
    private Set $set;

    protected function setUp(): void
    {
        $this->set = new Set();
    }

    public function testContainsScalar(): void
    {
        $this->set->add('a');

        $this->assertFalse($this->set->contains('b'));
        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasics('foo')));
        $this->assertFalse($this->set->contains(new \DateTime('today')));
    }

    public function testContainsObjectWithHandler(): void
    {
        $this->set->add(new \DateTime('today'));

        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasics('foo')));
        $this->assertFalse($this->set->contains('a'));

        $this->assertTrue($this->set->contains(new \DateTime('today')));
    }

    public function testContainsObject(): void
    {
        $this->set->add(new ObjectThatImplementsBasics('foo'));

        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasics('bar')));
        $this->assertFalse($this->set->contains('a'));
        $this->assertFalse($this->set->contains(new \DateTime()));

        $this->assertTrue($this->set->contains(new ObjectThatImplementsBasics('foo')));
    }

    public function testReverse(): void
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(['a', 'b'], $this->set->all());

        $reversedSet = $this->set->reverse();
        $this->assertEquals(['a', 'b'], $this->set->all());
        $this->assertEquals(['b', 'a'], $reversedSet->all());
    }

    public function testMap(): void
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(['a', 'b'], $this->set->all());

        $newSet = $this->set->map(function ($char) {
            if ('a' === $char) {
                return 'c';
            } elseif ('b' === $char) {
                return 'd';
            }

            return $char;
        });

        $this->assertEquals(['a', 'b'], $this->set->all());
        $this->assertEquals(['c', 'd'], $newSet->all());
    }

    public function testRemoveScalar(): void
    {
        $this->set->add('a');
        $this->assertCount(1, $this->set);

        $this->set->remove('b');
        $this->assertCount(1, $this->set);

        $this->set->remove('a');
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testRemoveObjectWithHandler(): void
    {
        $this->set->add(new \DateTime('today'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('-2 days'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('today'));
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testRemoveObject(): void
    {
        $this->set->add(new ObjectThatImplementsBasics('foo'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new ObjectThatImplementsBasics('bar'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new ObjectThatImplementsBasics('foo'));
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testAddScalar(): void
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->set->add('a');

        $this->assertEquals(['a', 'b'], $this->set->all());
    }

    public function testAddObject(): void
    {
        $this->set->add(new ObjectThatImplementsBasics('foo'));
        $this->set->add(new ObjectThatImplementsBasics('bar'));
        $this->set->add(new ObjectThatImplementsBasics('foo'));

        $this->assertEquals(
            [
                new ObjectThatImplementsBasics('foo'),
                new ObjectThatImplementsBasics('bar'),
            ],
            $this->set->all()
        );
    }

    public function testAddObjectWithHandler(): void
    {
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')));
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')));
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('US/Pacific')));

        $this->assertEquals(
            [
                (new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')),
                (new \DateTime('today'))->setTimezone(new \DateTimeZone('US/Pacific')),
            ],
            $this->set->all()
        );
    }
}

class ObjectThatImplementsBasics implements ObjectBasics
{
    public function __construct(private mixed $value)
    {
    }

    public function hash(): string
    {
        return 'foo'; // This is not recommended in the real-world.
    }

    public function equals(ObjectBasics $other): bool
    {
        if ($this === $other) {
            return true;
        }
        if (!$other instanceof ObjectThatImplementsBasics) {
            return false;
        }

        return $this->value === $other->value;
    }
}
