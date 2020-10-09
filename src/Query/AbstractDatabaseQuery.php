<?php declare(strict_types=1);

namespace GW\DQO\Query;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use GW\DQO\DatabaseSelectBuilder;
use GW\DQO\Table;
use function in_array;

abstract class AbstractDatabaseQuery
{
    private DatabaseSelectBuilder $builder;
    private Table $builderTable;
    /** @var string[] */
    private array $joinedAliases = [];

    public function __construct(DatabaseSelectBuilder $builder, Table $table)
    {
        $this->builderTable = $table;
        $this->builder = $builder
            ->withTypes(
                [DateTimeImmutable::class => 'DateTimeImmutable']
            )
            ->from($table);
    }

    /**
     * @return $this
     */
    public function resetForConnection(Connection $connection): self
    {
        $copy = clone $this;
        $copy->builder = $this->builder
            ->resetForConnection($connection)
            ->from($this->builderTable);

        return $copy;
    }

    /**
     * @return $this
     * @param array<string, mixed> $params
     * @param array<string, string> $types
     */
    public function join(Table $join, string $condition, array $params = [], array $types = []): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->join($join, $condition)->withParameters($params, $types);
        $clone->joinedAliases[] = $join->alias();

        return $clone;
    }

    /**
     * @return $this
     * @param array<string, mixed> $params
     * @param array<string, string> $types
     */
    public function leftJoin(Table $join, string $condition, array $params = [], array $types = []): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->leftJoin($join, $condition)->withParameters($params, $types);
        $clone->joinedAliases[] = $join->alias();

        return $clone;
    }

    /**
     * @return $this
     */
    public function joinOnce(Table $join, string $condition): self
    {
        if (in_array($join->alias(), $this->joinedAliases, true)) {
            return $this;
        }

        return $this->join($join, $condition);
    }

    /**
     * @return $this
     * @param array<string, mixed> $params
     * @param array<string, string> $types
     */
    public function where(string $condition, array $params = [], array $types = []): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->where($condition, $params, $types);

        return $clone;
    }

    /**
     * @return $this
     * @param array<string, mixed> $params
     * @param array<string, string> $types
     */
    public function having(string $condition, array $params = [], array $types = []): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->having($condition, $params, $types);

        return $clone;
    }

    /**
     * @return $this
     */
    public function groupBy(string ...$groupsBy): self
    {
        $clone = clone $this;

        foreach ($groupsBy as $groupBy) {
            $clone->builder = $clone->builder->groupBy($groupBy);
        }

        return $clone;
    }

    /**
     * @return $this
     */
    public function offsetLimit(int $offset, ?int $limit = null): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->offsetLimit($offset, $limit);

        return $clone;
    }

    /**
     * @return $this
     */
    public function limitOne(): self
    {
        return $this->offsetLimit(0, 1);
    }

    /**
     * @return $this
     */
    public function orderBy(string $field, string $direction): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->orderBy($field, $direction);

        return $clone;
    }

    /**
     * @return $this
     */
    public function select(string ...$fields): self
    {
        $clone = clone $this;
        $clone->builder = $clone->builder->select(...$fields);

        return $clone;
    }

    public function exists(): bool
    {
        return $this->builder->select('1')->fetchColumn() === '1';
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    public function distinctCount(string $column): int
    {
        return $this->builder->count("DISTINCT $column");
    }

    public function builder(): DatabaseSelectBuilder
    {
        return $this->builder;
    }
}
