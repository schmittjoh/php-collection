<?php

namespace Collection\Tests;

use Collection\ObjectBasicsInterface;
use Collection\Set;

class SetTest extends \PHPUnit_Framework_TestCase
{
    /** @var Set */
    private $set;

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
        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasicsInterface('foo')));
        $this->assertFalse($this->set->contains(new \DateTime('today')));
    }

    public function testContainsObjectWithHandler()
    {
        $this->set->add(new \DateTime('today'));

        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasicsInterface('foo')));
        $this->assertFalse($this->set->contains('a'));

        $this->assertTrue($this->set->contains(new \DateTime('today')));
    }

    public function testContainsObject()
    {
        $this->set->add(new ObjectThatImplementsBasicsInterface('foo'));

        $this->assertFalse($this->set->contains(new ObjectThatImplementsBasicsInterface('bar')));
        $this->assertFalse($this->set->contains('a'));
        $this->assertFalse($this->set->contains(new \DateTime()));

        $this->assertTrue($this->set->contains(new ObjectThatImplementsBasicsInterface('foo')));
    }

    public function testReverse()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(array('a', 'b'), $this->set->all());

        $reversedSet = $this->set->reverse();
        $this->assertEquals(array('a', 'b'), $this->set->all());
        $this->assertEquals(array('b', 'a'), $reversedSet->all());
    }

    public function testMap()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->assertEquals(array('a', 'b'), $this->set->all());

        $newSet = $this->set->map(function($char) {
            if ($char === 'a') {
                return 'c';
            } elseif ($char === 'b') {
                return 'd';
            }

            return $char;
        });

        $this->assertEquals(array('a', 'b'), $this->set->all());
        $this->assertEquals(array('c', 'd'), $newSet->all());
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
        $this->set->add(new \DateTime('today'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('-2 days'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new \DateTime('today'));
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testRemoveObject()
    {
        $this->set->add(new ObjectThatImplementsBasicsInterface('foo'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new ObjectThatImplementsBasicsInterface('bar'));
        $this->assertCount(1, $this->set);

        $this->set->remove(new ObjectThatImplementsBasicsInterface('foo'));
        $this->assertCount(0, $this->set);
        $this->assertTrue($this->set->isEmpty());
    }

    public function testAddScalar()
    {
        $this->set->add('a');
        $this->set->add('b');
        $this->set->add('a');

        $this->assertEquals(array('a', 'b'), $this->set->all());
    }

    public function testAddObject()
    {
        $this->set->add(new ObjectThatImplementsBasicsInterface('foo'));
        $this->set->add(new ObjectThatImplementsBasicsInterface('bar'));
        $this->set->add(new ObjectThatImplementsBasicsInterface('foo'));

        $this->assertEquals(
            array(
                new ObjectThatImplementsBasicsInterface('foo'),
                new ObjectThatImplementsBasicsInterface('bar')
            ),
            $this->set->all()
        );
    }

    public function testAddObjectWithHandler()
    {
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')));
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')));
        $this->set->add((new \DateTime('today'))->setTimezone(new \DateTimeZone('US/Pacific')));

        $this->assertEquals(
            array(
                (new \DateTime('today'))->setTimezone(new \DateTimeZone('UTC')),
                (new \DateTime('today'))->setTimezone(new \DateTimeZone('US/Pacific')),
            ),
            $this->set->all()
        );
    }

    protected function setUp()
    {
        $this->set = new Set();
    }
}

class ObjectThatImplementsBasicsInterface implements ObjectBasicsInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function hash()
    {
        return 'foo'; // This is not recommended in the real-world.
    }

    public function equals(ObjectBasicsInterface $other)
    {
        if ($this === $other) {
            return true;
        }
        if ( ! $other instanceof ObjectThatImplementsBasicsInterface) {
            return false;
        }

        return $this->value === $other->value;
    }
}