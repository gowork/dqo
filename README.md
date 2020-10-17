# Database Query Objects

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

  - [ ] switch to `nikic/parser` for code generation
  - [ ] generate queries for tables
  - [ ] update readme
  - [ ] add command to update table/row with new fields
  - [ ] auto run cs fixer on generated files (if installed)

## About

Used at:

 - [gowork.pl](https://www.gowork.pl)
 - [gowork.fr](https://gowork.fr)
 - [gowork.com](https://es.gowork.com)
