# Database Query Objects

## Install

```bash
composer require gowork/dqo
```

## Setup

### Symfony

Add the DatabaseAccessGeneratorBundle to your application's kernel:

```php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new GW\DQO\Symfony\DatabaseAccessGeneratorBundle(),
        // ...
    );
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

  - [ ] switch to `nikic/parser` for code generation
  - [ ] generate queries for tables
  - [ ] update readme
  - [ ] add command to update table/row with new fields

## About

Used at:

 - [gowork.pl](https://www.gowork.pl)
 - [gowork.fr](https://gowork.fr)
 - [gowork.com](https://es.gowork.com)
