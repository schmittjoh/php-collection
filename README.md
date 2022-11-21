PHP Collection
==============

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0448ddfd-68f8-4c77-a37b-2f3883652a68/mini.png)](https://insight.sensiolabs.com/projects/0448ddfd-68f8-4c77-a37b-2f3883652a68)
[![Build Status](https://travis-ci.org/Imunhatep/php-collection.svg?branch=master)](https://travis-ci.org/Imunhatep/php-collection)

PHP Collection library.

Based on J. M. Schmitt work, refactored and updated - now requires PHP 8.0. 
Updated with several useful methods like ::flatMap(), ::map(), ::foldLeft(), ::exists(), ::headOption(),  ::lastOption(), ::tail()  and e.t.c. 
Inspired by Scala collections. 
Added types.

Installation
------------
PHP Collection can easily be installed via composer (in process)

```bash
composer require imunhatep/collection
```

or add it to your ``composer.json`` file.

Note
-------

Collections can be seen as more specialized arrays for which certain contracts are guaranteed.

Supported Collections:

- [Sequence](#sequence-anchor)

  - Keys: numerical, consequentially increasing, no gaps
  - Values: anything, duplicates allowed
  - Classes: ``Sequence``, ``SortedSequence``


- [Map](#map-anchor)

  - Keys: strings or objects, duplicate keys not allowed
  - Values: anything, duplicates allowed
  - Classes: ``Map``, ``LRUMap``


- [Set](#set-anchor)

  - Keys: not meaningful
  - Values: objects, or scalars, each value is guaranteed to be unique (see Set usage below for details)
  - Classes: ``Set``

General Characteristics:

- Collections are mutable (new elements may be added, existing elements may be modified or removed). Specialized
  immutable versions may be added in the future though.
- Equality comparison between elements are always performed using the shallow comparison operator (===).
- Sorting algorithms are unstable, that means the order for equal elements is undefined (the default, and only PHP behavior).


Usage
-----
Collection classes provide a rich API.

Sequence <a name="sequence-anchor"></a>
-------------------------------------------------

```php

    // Read Operations
    $seq = new Sequence([0, 2, 3, 2]);
    $seq->get(2); // int(3)
    $seq->all(); // [0, 2, 3, 2]

    $seq->head(); // Some(0)
    $seq->tail(); // Some(2)

    // Write Operations
    $seq = new Sequence([1, 5]);
    $seq->get(0); // int(1)
    
    $seq
      ->update(0, 4);
      ->get(0); // int(4)
    
    $seq
      ->remove(0)
      ->get(0); // int(5)

    $seq = new Sequence([1, 4]);
    $seq
      ->add(2)
      ->all(); // [1, 4, 2]
    
    $seq
      ->addAll([4, 5, 2])
      ->all(); // [1, 4, 2, 4, 5, 2]

    // Sort
    $seq = new Sequence([0, 5, 4, 2]);
    $seq
      ->sortWith(fn($a, $b) => ($a - $b));
      ->all(); // [0, 2, 4, 5]
```

Maps <a name="map-anchor"></a>
------------------------------

```php

    // Read Operations
    $map = new Map(['foo' => 'bar', 'baz' => 'boo']);
    $map->get('foo'); // Some('bar')
    $map->get('foo')->get(); // string('bar')
    $map->keys(); // ['foo', 'baz']
    $map->values(); // ['bar', 'boo']

    $map->headOption(); // Some(['key', 'value'])
    $map->head();       // null | ['key', 'value']
    $map->lastOption(); // Some(['key', 'value'])
    $map->last();       // null | ['key', 'value']
    
    $map->headOption()->getOrElse([]); // ['foo', 'bar']
    $map->lastOption()->getOrElse([]); // ['baz', 'boo']
    
    $map->tail();            // ['baz' => 'boo']

    iterator_to_array($map); // ['foo' => 'bar', 'baz' => 'boo']
    $map->all()              // ['foo' => 'bar', 'baz' => 'boo']

    // Write Operations
    $map = new Map();
    $map->set('foo', 'bar');
    $map->setAll(['bar' => 'baz', 'baz' => 'boo']);
    $map->remove('foo');

    // Sort
    $map->sortWith('strcmp');

    // Transformation
    $map->map(fn($k,$v) => ($value * 2));
    
    $map->flatMap(fn($k,$v) => (new Map())->set($k, $v * 2) );
    
    $map->foldLeft(SomeObject $s, fn($s, $k, $v) => $s->add([$k, $v * 2]))
```

Set <a name="set-anchor"></a>
-----------------------------
In a Set each value is guaranteed to be unique. The ``Set`` class supports objects, and scalars as value. Equality
is determined via the following steps.

**Equality of Scalars**

    Scalar are considered equal if ``$a === $b`` is true.


```php
    class DateTimeHashable extends \DateTime implements HashableInterface
    {
        public function hash(): string
        {
            return $this->format("YmdP");
        }
    }
    
    $set = new Set();
    $set->add(new DateTimeHashable('today'));
    $set->add(new DateTimeHashable('today'));

    var_dump(count($set)); // int(1) -> the same date is not added twice

    foreach ($set as $date) {
        var_dump($date);
    }

    $set->all();
    $set->addSet($otherSet);
    $set->addAll($someElements);

    // Traverse
    $set->map(function($x) { return $x*2; });
    $set->flatMap(function($x) { return [$x*2, $x*4]; });
```
