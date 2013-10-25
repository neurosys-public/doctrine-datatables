Doctrine Datatables library
=====================================

Doctrine Datatables library provides a Doctrine2 wrapper around [Datatables](http://datatables.net/).
This library was created because

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
 * support doctrine2 associations (without validation)
 * support of doctrine query builder
 * support of column search with custom column definitions

Usage
-----

```php
$builder = new DatatableBuilder($entityManager, $_GET);
$builder
    ->from('Foo\Bar\Entity\Sample')
    ->add('text')
    ->add('number')
    ->add('date')
    ;

$response = $builder->getDatatable()
    ->getResponseArray()
    ;

// return response here
// header ('Content-Type', 'application/json');
// echo json_encode($response);
```

Warning
-------

This library is still in development, API is likely to change.


License
-------

Doctrine Datatables is licensed under the MIT license.
