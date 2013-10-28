Doctrine Datatables library [![Build Status](https://travis-ci.org/neurosys-pl/doctrine-datatables.png)](https://travis-ci.org/neurosys-pl/doctrine-datatables)
===========================

Doctrine Datatables library provides a Doctrine2 server side processing for [Datatables](http://datatables.net/).

This library was created because existing libraries lack of flexibility around field types and field filtering.

Installation
------------

You can install this library using composer

```
composer require neurosys/doctrine-datatables
```

or add the package name to your composer.json

```js
"require": {
    ...
    "neurosys/doctrine-datatables": "dev-master"
}
```

Features
--------
 * support doctrine2 associations using dot notation (without relation existence validation)
 * support of doctrine query builder
 * support of column search with custom column definitions (ex. number, date, composed fields)

It does not support global search (yet)

Usage
-----
```php
$builder = new DatatableBuilder($entityManager, $_GET);
$builder
    ->from('Foo\Bar\Entity\Sample')
    ->add('text')                  // field name will be resolved from request (mDataProp_X)
    ->add('number', 'price')       // field name will be forced to be 'price'
    ->add('boolean', 'foo.active') // related entity will be leftJoin'ed and field 'active' will be fetched
    ;

$response = $builder->getDatatable()
    ->getResponseArray()
    ;

// now you can simply return a response
// header ('Content-Type', 'application/json');
// echo json_encode($response);
```

If fields are simple texts than there is no need of adding any field to a datatable builder, fields will be automatically generated from mDataProp_x
```php
$builder
    ->from('Foo\Bar\Entity\Sample')
    ;
```

Composed fields example:

```php
$builder
    ->from('Foo\Bar\Entity\Sample')
    ->add('text')
    ->with('fullName') // composed field name (not existing in database)
        ->add('text', 'firstName') // field names are required here
        ->add('text', 'lastName')
    ->end()
    ->add('date')
    ;
```

Custom query builder example:
```php
$responseArray = $builder
    ->setQueryBuilder($customQueryBuilder)
    ->getDatatable()
    ->getResponseArray();
```

Available field types
---------------------

 * text
 * number
 * date
 * boolean
 * choice

Creating custom fields
----------------------
To create custom field create a class that inherits from NeuroSYS\DoctrineDatatables\Field\Field
```php
<?php
namespace Baz\Bar\Field;

use NeuroSYS\DoctrineDatatables\Field\Field;

class FooField extends Field
{
    public function filter(QueryBuilder $qb)
    {
        $qb->setParameter($this->getName(), $this->getSearch().'%');

        return $qb->expr()->like($this->getFullName(), ':' . $this->getName()); // return Expr
    }

    public function format(array $values)
    {
        return $values[$this->getAlias()] + 1; // return increased value
    }
}
```
There is also overridable *select* and *order*.

```php
$registry = new FieldRegistry();
$registry->register('foo', '\Baz\Bar\Field\Foo');

$builder = new DatatableBuilder($entityManager, $_GET, $registry);
$builder
    ->from('Foo\Bar\Entity\Sample')
    ->add('foo')
    ->add('text')
    ;

$response = $builder->getDatatable()
    ->getResponseArray()
    ;

// now you can simply return a response
// header ('Content-Type', 'application/json');
// echo json_encode($response);
```

Warning
-------

This library is still in development, API is likely to change.

License
-------

Doctrine Datatables is licensed under the MIT license.
