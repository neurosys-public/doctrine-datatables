Doctrine Datatables library [![Build Status](https://travis-ci.org/neurosys-pl/doctrine-datatables.png)](https://travis-ci.org/neurosys-pl/doctrine-datatables)
===========================

Doctrine Datatables library provides a Doctrine2 server side processing for [Datatables](http://datatables.net/).

This library was created because existing libraries lack of flexibility around field types and field filtering.
This library does not provide any JavaScript code generation nor datatables.js sources, you need to install and run datatables.js yourself.

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
 * support of doctrine query builder
 * support of column search with custom column definitions (ex. number, date, composed fields)

It does not support global search (yet)

Usage
-----
```php
$builder = new TableBuilder($entityManager, $_GET);
$builder
    ->from('Foo\Bar\Entity\Sample', 's')
    ->add('text', 's.name')      // field name will be resolved from request (mDataProp_X)
    ->add('number', 's.price')   // field will be a number field which can be filtered by value range
    ->add('boolean', 's.active')
    ;

$response = $builder->getTable()
    ->getResponseArray()
    ;

// now you can simply return a response
// header ('Content-Type', 'application/json');
// echo json_encode($response);
```

Composed fields example:

```php
$builder
    ->from('Foo\Bar\Entity\Sample', 's')
    ->add('text', 's.name')
    ->add('text', 's.firstName, s.lastName')
    ->add('date')
    ;
```

Custom query builder example:
```php
$responseArray = $builder
    ->setQueryBuilder($customQueryBuilder)
    ->add('text', 's.foo')
    ->getTable()
    ->getResponseArray();
```

Available field types
---------------------

 * text
 * number
 * date
 * boolean
 * choice

Warning
-------

This library is still in development, API is most likely to change.

License
-------

Doctrine Datatables is licensed under the MIT license.
