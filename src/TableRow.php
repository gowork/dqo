<?php

namespace GW\DQO;

use GW\Gowork\Generic\DateTimeTo;

abstract class TableRow
{
    /** @var array|object */
    private $row;

    /** @var \Closure */
    private $getter;

    /** @var Table */
    private $table;

    /**
     * @param object|array $row
     */
    public function __construct($row, Table $table)
    {
        $this->row = $row;
        $this->initGetter($row);
        $this->table = $table;
    }

    /**
     * @return string|int|null
     */
    public function get(string $field)
    {
        return ($this->getter)($field);
    }

    protected function getNullableString(string $field): ?string
    {
        return $this->getThrough('strval', $field);
    }

    protected function getString(string $field): string
    {
        return (string)$this->get($field);
    }

    protected function getNullableInt(string $field): ?int
    {
        return $this->getThrough('intval', $field);
    }

    protected function getInt(string $field): int
    {
        return (int)$this->get($field);
    }

    protected function getNullableBool(string $field): ?bool
    {
        return $this->getThrough('boolval', $field);
    }

    protected function getBool(string $field): bool
    {
        return (bool)$this->get($field);
    }

    protected function getDateTime(string $field): ?\DateTime
    {
        return $this->getThrough([DateTimeTo::class, 'mutable'], $field);
    }

    protected function getDateTimeImmutable(string $field): ?\DateTimeImmutable
    {
        return $this->getThrough([DateTimeTo::class, 'immutable'], $field);
    }

    /**
     * @param callable $factory function($value): mixed
     * @return mixed|null
     */
    protected function getThrough(callable $factory, string $field)
    {
        $value = $this->get($field);

        return $value !== null ? $factory($value) : null;
    }

    private function initGetter($row): void
    {
        if (is_array($row) || $row instanceof \ArrayAccess) {
            $this->getter = function (string $field) {
                return $this->row[$this->table->fieldAlias($field)] ?? null;
            };

            return;
        }

        if (is_object($row)) {
            $this->getter = function (string $field) {
                return $this->row->{$this->table->fieldAlias($field)} ?? null;
            };

            return;
        }

        throw new \InvalidArgumentException('Unsupported database query row format.');
    }
}
