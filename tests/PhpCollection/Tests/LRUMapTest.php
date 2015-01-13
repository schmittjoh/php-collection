<?php

namespace PhpCollection\Tests;

use PhpCollection\LRUMap;
use PhpCollection\Map;
use PhpCollection\Tests\MapTest;

class LRUMapTest extends MapTest
{

    public function testRecentHead()
    {
        $lru = new LRUMap(3);
        $lru->set('one', 1);
        $lru->set('two', 2);
        $lru->set('three', 3);

        $first = $lru->first();
        $this->assertTrue($first->isDefined());
        $this->assertEquals(1, $first->get()[1]);

        $last = $lru->last();
        $this->assertTrue($last->isDefined());
        $this->assertEquals(3, $last->get()[1]);

        // make 'two' most recent
        $lru->get('two');
        $last = $lru->last();
        $this->assertTrue($last->isDefined());
        $this->assertEquals(2, $last->get()[1]);
    }

    public function testOverflow()
    {
        $this->map->set('one', 1);
        $this->assertCount(4, $this->map);
        $this->map->set('two', 2);
        $this->assertCount(4, $this->map);
        $this->map->set('three', 3);
        $this->assertCount(4, $this->map);

    }


    protected function setUp()
    {
        $this->map = new LRUMap(4);
        $this->map->setAll(array(
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'boo',
        ));
    }
}
