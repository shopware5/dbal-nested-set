Doctrine DBAL Nested Set
====================

A multi root Nested Set implementation for DBAL users.

## Description

This library provides you write, read and inspection classes for nested sets. Solely relying on the Doctrine DBAL.

Contrary to other solutions this library has clear boundaries and leaves the software design up to you.

 * No tree abstraction - just nested sets
 * No entities - just method calls with plain parameters

## Installation

Use composer to install the library

```sh
> composer require shopware/dbal-nested-set

```

## Usage

You always need a configuration that sets up the basic column names of your implementation:

```php

use Shopware\DbalNestedSet\NestedSetConfig;

$config = new NestedSetConfig(
    'id', // Primary key column name
    'left', // left column name
    'right',  // right column name
    'level' // level column name
);
```

Then you can use the `NestedSetFactory` to create the different classes of the library.

```php
use Shopware\DbalNestedSet\NestedSetFactory;
use Doctrine\DBAL\Connection;

$writer = NestedSetFactory::createWriter($dbalConnection, $config);

```

### Modify the tree

The library provides a `NestedSetWriter` class that contains all insert, move and update operations. All operations should be reminiscent of `Doctrine\DBAL\Connection::insert()` and `Doctrine\DBAL\Connection::update()` and just require plain data.

As an example you can use this to create a tree

```php

$writer = NestedSetFactory::createWriter($dbalConnection, $config);

// create a Root node
$writer->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);

// create subnodes
$writer->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Men']);
$writer->insertAsNextSibling('tree', 'root_id', 2, ['name' => 'Women']);
$writer->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Suits']);
$writer->insertAsFirstChild('tree', 'root_id', 3, ['name' => 'Dresses']);
$writer->insertAsNextSibling('tree', 'root_id', 5, ['name' => 'Skirts']);
$writer->insertAsNextSibling('tree', 'root_id', 6, ['name' => 'Blouses']);
$writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Jackets']);
$writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Slacks']);
$writer->insertAsFirstChild('tree', 'root_id', 5, ['name' => 'Evening Gowns']);
$writer->insertAsNextSibling('tree', 'root_id', 10, ['name' => 'Sun Dresses']);
```

And then use the writer to move nodes around

```php
$writer->moveAsNextSibling('tree', 'root_id', 4, 7);
```

### Inspect nodes

You may want to retrieve information about different nodes. This can be done through the `NestedSetTableNodeInspector`.

```php
$inspector = NestedSetFactory::createTableNodeInspector($connection, new NestedSetConfig('id', 'left', 'right', 'level'));

$inspector->isLeaf('tree', 'root_id', 9); // true | false
$inspector->isAncestor('tree', 'root_id', 1, 2) // true | false
```

### Inspect the tree

The `NestedSetQueryFactory` helps retrieve a set of nodes from the tree. Since the library has no concept of entities it will only prepare query builders for you ready for you to add selects joins and other conditions.


```php
$queryFactory = NestedSetFactory::createQueryFactory($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
$data = $queryFactory
            ->createChildrenQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*')
            ->execute()
            ->fetchAll();
```

## Local development

If you want to develop locally you may have to configure the database access through a little shell script:

```bash
#!/usr/bin/env bash

export DB_USER='foo'
export DB_PASSWORD='bar'
export DB_HOST='baz'

bin/phpunit

```



