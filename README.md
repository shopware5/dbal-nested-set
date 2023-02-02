Doctrine DBAL Nested Set
====================

## Used in B2B Suite for Shopware 5

A multi root nested set implementation for DBAL users.

## Description

This library provides you write, read and inspection classes for nested sets with multiple root nodes per table. Solely relying on the Doctrine DBAL.

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

### Create a tree

You may want to create a normalized schema for nested set tables, this can be accomplished through the `NestedSetTableFactory`. It will create the base DDL for a tree with indexes. So if you want to add a simple tree with a name column and an autoincrement id it will look like this:

```php
$tableFactory = NestedSetFactory::createTableFactory($connection, $config);

$schema = new \Doctrine\DBAL\Schema\Schema();
$table = $tableFactory->createTable(
    $schema,
    'tree', // table name
    'root_id' // nested set root id
);
$table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
$table->addColumn('name', 'string', ['length' => 255]);
$table->setPrimaryKey(['id']);

$addSql = $schema->toSql($connection->getDatabasePlatform());
```

Of course this is optional and may be accomplished through any schema configuration tool.

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
$inspector = NestedSetFactory::createTableNodeInspector($connection, $config);

$inspector->isLeaf('tree', 'root_id', 9); // true | false
$inspector->isAncestor('tree', 'root_id', 1, 2) // true | false
```

### Inspect the tree

The `NestedSetQueryFactory` helps retrieve a set of nodes from the tree. Since the library has no concept of entities it will only prepare query builders for you ready to add selects, joins and other conditions.

```php
$queryFactory = NestedSetFactory::createQueryFactory($connection, $config);
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
export DB_NAME='dbal_nested_set'

bin/phpunit
```
