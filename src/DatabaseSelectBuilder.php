<?php

namespace GW\DQO;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GW\DQO\Generator\Table;
use GW\Value\ArrayValue;
use GW\Value\Wrap;

final class DatabaseSelectBuilder
{
    public const DEFAULT_LIMIT = 20;

    /** @var QueryBuilder */
    private $builder;

    /** @var Table|null */
    private $from;

    /** @var string[] [class => doctrine type, ...] */
    private $types;

    /** @var string[] [model field => query field, ...] */
    private $sortMap = [];

    /** @var bool */
    private $sliced = false;

    public function __construct(
        Connection $connection,
        array $types = [\DateTimeImmutable::class => 'DateTimeImmutable']
    ) {
        $this->builder = $connection->createQueryBuilder();
        $this->types = $types;
    }

    public function __clone()
    {
        $this->builder = clone $this->builder;
    }

    public function resetForConnection(Connection $connection): self
    {
        $copy = clone $this;
        $copy->builder = $connection->createQueryBuilder();
        $copy->sliced = false;

        return $copy;
    }

    public function withTypes(array $types): self
    {
        $copy = clone $this;
        $copy->types = array_merge($this->types, $types);

        return $copy;
    }

    public function withSortMap(array $sortMap): self
    {
        $copy = clone $this;
        $copy->sortMap = array_merge($this->sortMap, $sortMap);

        return $copy;
    }

    public function from(Table $table): self
    {
        $copy = clone $this;
        $copy->from = $table;
        $copy->builder->from($table->table(), $table->alias());

        return $copy;
    }

    public function join(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder->join($this->from->alias(), $join->table(), $join->alias(), $condition);

        return $copy;
    }

    public function leftJoin(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder->leftJoin($this->from->alias(), $join->table(), $join->alias(), $condition);

        return $copy;
    }

    public function rightJoin(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder->rightJoin($this->from->alias(), $join->table(), $join->alias(), $condition);

        return $copy;
    }

    public function where(string $condition, array $params = [], array $types = []): self
    {
        $copy = clone $this;
        $copy->builder->andWhere($condition);
        foreach ($params as $key => $value) {
            $copy->builder->setParameter($key, $value, $types[$key] ?? $this->paramType($value));
        }

        return $copy;
    }

    public function select(string ...$columns): self
    {
        $copy = clone $this;
        $copy->builder->select(...$columns);

        return $copy;
    }

    /**
     * @return bool|string
     */
    public function fetchColumn(int $index = 0)
    {
        return (clone $this->builder)->setMaxResults(1)->execute()->fetchColumn($index);
    }

    public function fetchDate(int $index = 0): ?\DateTimeImmutable
    {
        $date = $this->fetchColumn($index);

        return $date ? new \DateTimeImmutable($date) : null;
    }

    /**
     * @return array[]
     */
    public function fetchAll(): array
    {
        return (clone $this->builder)->execute()->fetchAll();
    }

    public function fetch(): ?array
    {
        $result = (clone $this->builder)->execute()->fetch();

        return $result !== false ? $result : null;
    }

    public function wrapAll(): ArrayValue
    {
        return Wrap::array($this->fetchAll());
    }

    public function count(): int
    {
        return (int)$this->select('COUNT(1)')->fetchColumn();
    }

    public function offsetLimit(int $offset, ?int $limit = null): self
    {
        $copy = clone $this;
        $copy->sliced = true;
        $copy->builder->setFirstResult($offset);

        if ($limit) {
            $copy->builder->setMaxResults($limit ?? self::DEFAULT_LIMIT);
        }

        return $copy;
    }

    public function orderBy(string $field, string $direction): self
    {
        $copy = clone $this;
        $copy->builder->addOrderBy($this->sortMap[$field] ?? $field, $direction);

        return $copy;
    }

    public function resetOrderBy(): self
    {
        $copy = clone $this;
        $copy->builder->resetQueryPart('orderBy');

        return $copy;
    }

    public function withParameter(string $key, $value, $type = null): self
    {
        $copy = clone $this;
        $copy->builder->setParameter($key, $value, $type ?? $this->paramType($value));

        return $copy;
    }

    public function groupBy(string $groupBy): self
    {
        $copy = clone $this;
        $copy->builder->groupBy($groupBy);

        return $copy;
    }

    public function resetGroupBy(): self
    {
        $copy = clone $this;
        $copy->builder->resetQueryPart('groupBy');

        return $copy;
    }

    public function getSQL(): string
    {
        return $this->builder->getSQL();
    }

    public function getDbalBuilder(): QueryBuilder
    {
        return clone $this->builder;
    }

    public function isSliced(): bool
    {
        return $this->sliced;
    }

    /**
     * @return string|int|null
     */
    private function paramType($object)
    {
        if (\is_array($object)) {
            return Connection::PARAM_STR_ARRAY;
        }

        if (!\is_object($object)) {
            return null;
        }

        return $this->types[\get_class($object)] ?? null;
    }

    private function assertCanJoin(): void
    {
        if ($this->from === null) {
            throw new \RuntimeException('FROM must be declared before JOIN');
        }
    }
}
