<?php

namespace GW\DQO;

use GW\Value\Wrap;

abstract class Table
{
    /** @var string */
    private $table;

    /** @var string */
    private $alias;

    /** @var string[] */
    private $fields;

    public function __construct(?string $alias = null, ?string $table = null)
    {
        $this->table = $table ?? $this->resolveTableName();
        $this->fields = $this->resolveTableFields();
        $this->alias = $alias ?? $this->table();
    }

    final public function table(): string
    {
        return $this->table;
    }

    final public function alias(): string
    {
        return $this->alias;
    }

    public function selectAll(): array
    {
        return $this->select(...$this->fields);
    }

    public function select(string ...$fields): array
    {
        return Wrap::array($fields)
            ->map([$this, 'selectField'])
            ->toArray();
    }

    public function selectField(string $field): string
    {
        $path = $this->fieldPath($field);
        $alias = $this->fieldAlias($field);

        return "{$path} as {$alias}";
    }

    public function fieldPath(string $field): string
    {
        return "{$this->alias}.{$field}";
    }

    public function fieldAlias(string $field): string
    {
        return "{$this->alias}_{$field}";
    }

    private function resolveTableName(): string
    {
        return Wrap::string(static::class)
            ->explode('\\')
            ->last()
            ->substring(0, -\strlen('Table'))
            ->replacePattern('/([A-Z])/', '_$1')
            ->trimLeft('_')
            ->lower()
            ->toString();
    }

    private function resolveTableFields(): array
    {
        return \array_values((new \ReflectionClass(static::class))->getConstants());
    }
}
