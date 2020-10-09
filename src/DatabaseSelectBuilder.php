<?php declare(strict_types=1);

namespace GW\DQO;

use DateTimeImmutable;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GW\Value\ArrayValue;
use GW\Value\Wrap;
use RuntimeException;
use function array_merge;
use function get_class;
use function is_array;
use function is_object;

final class DatabaseSelectBuilder
{
    public const DEFAULT_LIMIT = 20;
    private QueryBuilder $builder;
    private ?Table $from = null;
    /** @var string[] [class => doctrine type, ...] */
    private array $types;
    /** @var string[] [model field => query field, ...] */
    private array $sortMap = [];
    private int $startOffset = 0;
    private bool $sliced = false;

    public function __construct(
        Connection $connection,
        array $types = [DateTimeImmutable::class => 'DateTimeImmutable']
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

    /** @param array<string, string> $types */
    public function withTypes(array $types): self
    {
        $copy = clone $this;
        $copy->types = array_merge($this->types, $types);

        return $copy;
    }

    /** @param array<string, string> $sortMap */
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

    public function having(string $condition, array $params = [], array $types = []): self
    {
        $copy = clone $this;
        $copy->builder->andHaving($condition);
        foreach ($params as $key => $value) {
            $copy->builder->setParameter($key, $value, $types[$key] ?? $this->paramType($value));
        }

        return $copy;
    }

    public function select(string ...$columns): self
    {
        $copy = clone $this;
        $copy->builder->select(
            ...array_map(
                function (string $column): string {
                    if (strpos($column, '.') !== false && strpos($column, ' ') === false) {
                        return "$column " . str_replace('.', '_', $column);
                    }

                    return $column;
                },
                $columns
            )
        );

        return $copy;
    }

    /**
     * @return false|string
     */
    public function fetchColumn(int $index = 0)
    {
        /** @var Statement $statement */
        $statement = (clone $this->builder)->setMaxResults(1)->execute();

        return $statement->fetchColumn($index);
    }

    public function fetchDate(int $index = 0): ?DateTimeImmutable
    {
        $date = $this->fetchColumn($index);

        return $date ? new DateTimeImmutable($date) : null;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function fetchAll(): array
    {
        /** @var Statement $statement */
        $statement = (clone $this->builder)->execute();

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function fetch(): ?array
    {
        /** @var Statement $statement */
        $statement = (clone $this->builder)->execute();
        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    public function wrapAll(): ArrayValue
    {
        return Wrap::array($this->fetchAll());
    }

    public function count(string $column = '1'): int
    {
        return (int)$this->select("COUNT({$column})")->fetchColumn();
    }

    public function offsetLimit(int $offset, ?int $limit = null): self
    {
        $copy = clone $this;
        $copy->sliced = $limit !== null;
        $copy->startOffset = $offset;
        $copy->builder->setFirstResult($offset);

        if ($limit) {
            $copy->builder->setMaxResults($limit ?? self::DEFAULT_LIMIT);
        }

        return $copy;
    }

    public function randomOrder(): self
    {
        $copy = clone $this;
        $copy->builder->addOrderBy('RAND()');

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

    /**
     * @param mixed $value
     * @param string|int|null $type
     */
    public function withParameter(string $key, $value, $type = null): self
    {
        $copy = clone $this;
        $copy->builder->setParameter($key, $value, $type ?? $this->paramType($value));

        return $copy;
    }

    public function withParameters(array $params = [], array $types = []): self
    {
        $copy = $this;
        $key = 0;

        foreach ($params as $name => $param) {
            $copy = $copy->withParameter($name, $param, $types[$key++] ?? null);
        }

        return $copy;
    }

    public function groupBy(string $groupBy): self
    {
        $copy = clone $this;
        $copy->builder->addGroupBy($groupBy);

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

    public function startOffset(): int
    {
        return $this->startOffset;
    }

    /**
     * @param mixed $object
     * @return string|int|null
     */
    private function paramType($object)
    {
        if (is_array($object)) {
            return Connection::PARAM_STR_ARRAY;
        }

        if (!is_object($object)) {
            return null;
        }

        return $this->types[get_class($object)] ?? null;
    }

    private function assertCanJoin(): void
    {
        if ($this->from === null) {
            throw new RuntimeException('FROM must be declared before JOIN');
        }
    }
}
