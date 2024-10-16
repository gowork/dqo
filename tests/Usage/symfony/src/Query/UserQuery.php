<?php

declare (strict_types=1);
namespace App\Query;

use GW\DQO\Query\AbstractDatabaseQuery;
use GW\DQO\DatabaseSelectBuilder;
use GW\DQO\Query\RowIterator;
use App\UserTable;
use App\UserRow;
final class UserQuery extends AbstractDatabaseQuery
{
    private UserTable $table;
    public function __construct(DatabaseSelectBuilder $builder)
    {
        $this->table = new UserTable();
        parent::__construct($builder, $this->table);
    }
    public function table(): UserTable
    {
        return $this->table;
    }
    /** @return iterable<UserRow> */
    public function all(string ...$fields): iterable
    {
        $builder = $this->builder()->select(...$fields ? $this->table->select(...$fields) : $this->table->selectAll());
        return new RowIterator($builder, fn(array $raw): UserRow => $this->table->createRow($raw));
    }
    public function first(): ?UserRow
    {
        return [...$this->offsetLimit(0, 1)->all()][0] ?? null;
    }
    public function single(int $id): ?UserRow
    {
        return $this->withId($id)->first();
    }
    public function withId(int $id): self
    {
        return $this->where("{$this->table->id()} = :id", ['id' => $id]);
    }
    public function withName(string $name): self
    {
        return $this->where("{$this->table->name()} = :name", ['name' => $name]);
    }
}
