# Database Query Objects

## Introduction

DQO provides an object representation of SQL database table, row and select query.

#### Features:
* Each database table can be described as `Table` class
* Enables column name completion in IDE while writing queries
* Provides a table columns enumeration as constants
* Each row returned from SELECT query can be described as `Row` class
* Table specific deserialization recipes can be added to corresponding `Row` class
* `Table` and `Row` classes code can be generated with Symfony console command
* Provides immutable `DatabaseSelectBuilder` for building SELECT queries

DQO is based on Doctrine DBAL and uses Doctrine Types for data deserialization and `Doctrine\DBAL\Connection` for query execution.

### `Table` definition

Classes representing specific database tables.
It contains enumeration of table columns as constants and simplifies field aliasing.
Multiple instances can be created with different aliases.

```php
final class UserTable extends GW\DQO\Table
{
    public const ID = 'id';
    public const EMAIL = 'email';
    public const NAME = 'name';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function email(): string
    {
        return $this->fieldPath(self::EMAIL);
    }

    public function name(): string
    {
        return $this->fieldPath(self::NAME);
    }
    
    public function createRow(array $raw): UserRow
    {
        return new UserRow($raw, $this);
    }
}

$userTable = new UserTable('user_alias');
$userTable->table(); // "user"
$userTable->alias(); // "user_alias"
$userTable->id(); // "user_alias.id"
$userTable->selectField(UserTable::ID); // "user_alias.id as user_alias_id"
```

### `TableRow` definition

Classes that can be created to unify data extracting and deserializing from corresponding table.

```php
final class UserRow extends ClientRow
{
    public function id(): UserId
    {
        return $this->getThroughType('UserId', UserTable::ID);
    }

    public function name(): string
    {
        return $this->getString(UserTable::NAME);
    }

    public function email(): Email
    {
        return Email::fromString($this->getString(UserTable::EMAIL));
    }

    public function optionalSecondEmail(): ?Email
    {
        return $this->getThrough([Email::class, 'fromString'], UserTable::OPTIONAL_SECOND_EMAIL);
    }

    public function about(): ?string
    {
        return $this->getNullableString(UserTable::NAME);
    }
}

$userTable = new UserTable();
$userRow = new UserRow($rowFromQuery, $userTable);
```

### Building SELECT query with `DatabaseSelectBuilder`

`DatabaseSelectBuilder` simplifies construction of SELECT statements using `Table` objects.

```php
/** @var Doctrine\DBAL\Connection $connection */
$builder = new GW\DQO\DatabaseSelectBuilder($connection);

$meTable = new UserTable('me');
$friendTable = new UserTable('friend');

$builder
    ->from($meTable)
    ->join($friendTable, "{$friendTable->id()} = {$meTable->friendId()}")
    ->where("{$meTable->username()} = :me", ['me' => 'John Doe'])
    ->select($friend->name())
    ->offsetLimit(0, 10);
```

#### SELECT column aliases

By default `TableRow` expects that table column used in SELECT part has alias as follows:  `table_alias.column_name as table_alias_column_name`.

There are 2 ways to create such alias:
* Use `Table` methods creating column aliases
  ```php
  $table = new UserTable();
  
  $builder = $builder->select(...$table->select(UserTable::ID, UserTable::email));
  // or
  $builder = $builder->select($table->selectField(UserTable::ID), $table->selectField(UserTable::email));
  // or
  $builder = $builder->select(...$table->selectAll());
  ```
* Use simply `$table->column()` when `select()` is after `table()` or `join()`
  ```php
  $table = new UserTable();
  
  // first add $table to builder so it can recognize `user.id`, `user.email` and create valid aliases...
  $builder = $builder->from($table);
  
  // ...then simply select
  $builder = $builder->select($table->id(), $table->email());
  ```

#### Query parameters

Query parameters can be specified directly in `where/having` method or provided later. 

```php
$builder = $builder->from($user)
    ->where("{$user->name()} = :name", ['name' => 'John Doe']) 
    ->having('orders > :limit', ['limit' => 10]);

// or 

$builder = $builder->from($user)
    ->where("{$user->name()} = :name") 
    ->withParameter('name', 'John Doe');

// or

$builder = $builder->from($user)
    ->where("{$user->name()} = :name") 
    ->withParameters(['name' => 'John Doe']);
```

Query parameter types can be specified as `where()` argument.

```php
$yesterday = new DateTime('yesterday');
$builder = $builder
    ->from($user)
    ->where("{$user->registered()} > :yesterday", ['yesterday' => $yesterday], ['yesterday' => 'datetime']); 
```

You can also define mapping of parameter classes to proper Doctrine type.

```php
$start = new DateTimeImmutable('first day of last month 00:00');
$end = new DateTimeImmutable('last day of last month 23:59');
$builder = $builder
    ->withTypes([DateTimeImmutable::class => 'datetime_immutable'])
    ->from($user)
    ->where("{$user->registered()} BETWEEN :start AND :end", ['start' => $start, 'end' => $end]); 
```


#### Fetching results

```php
/** @var array<string, mixed>|null $result one result row or null when there are no rows */
$result = $builder->fetch();

/** @var mixed|null $result one column from first result or null when no results */
$result = $builder->fetchColumn();

/** @var array<int, array<string, mixed>> $result fetch all result rows */
$result = $builder->fetchAll();

/** 
 * @var ArrayValue<array<string, mixed>> $result 
 * @see https://github.com/gowork/values
 */
$result = $builder->wrapAll();

/** @var int $result */
@result = $builder->count();
```

## Install

```bash
composer require gowork/dqo
```

## Setup

### Symfony

Add the DatabaseAccessGeneratorBundle to your application's kernel (only on `dev` environment):

```php
<?php
public function registerBundles(): array
{
    $bundles = [
        // ...
    ];
    
    if ($this->getEnvironment() === 'dev') {
        // ...
        $bundles[] = new GW\DQO\Symfony\DatabaseAccessGeneratorBundle();
    }
    ...
}
```

## Generate table class

```bash
dqo:generate-tables src/Database App/Database table_1 table_2
```

## Table query pattern

All queries should extends `GW\DQO\Query\AbstractDatabaseQuery`

## TODO

  - [ ] generate queries for tables
  - [ ] add command to update table/row with new fields

## About

Used at:

 - [gowork.pl](https://www.gowork.pl)
 - [gowork.fr](https://gowork.fr)
 - [gowork.com](https://es.gowork.com)
