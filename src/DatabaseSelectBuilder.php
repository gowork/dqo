<?php declare(strict_types=1);

namespace GW\DQO;

use DateTimeImmutable;
use Dazet\TypeUtil\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
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
    private Table $from;
    /** @var array<string, string|ParameterType|Type|ArrayParameterType> [class => doctrine type, ...] */
    private array $types;
    /** @var array<string, string> [model field => query field, ...] */
    private array $sortMap = [];
    private int $startOffset = 0;
    private bool $sliced = false;
    /** @var array<string, string> */
    private array $fieldSelect = [];

    /** @param array<string, string|ParameterType|Type|ArrayParameterType> $types */
    public function __construct(Connection $connection, array $types = [DateTimeImmutable::class => 'DateTimeImmutable'])
    {
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

    /** @param array<string, string|ParameterType|Type|ArrayParameterType> $types */
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
        $copy->builder = $copy->builder->from($table->table(), $table->alias());
        $copy->registerFieldSelectsForTable($table);

        return $copy;
    }

    public function join(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder = $copy->builder->join($this->from->alias(), $join->table(), $join->alias(), $condition);
        $copy->registerFieldSelectsForTable($join);

        return $copy;
    }

    public function leftJoin(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder = $copy->builder->leftJoin($this->from->alias(), $join->table(), $join->alias(), $condition);
        $copy->registerFieldSelectsForTable($join);

        return $copy;
    }

    public function rawLeftJoin(string $from, string $join, string $alias, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder->leftJoin($from, $join, $alias, $condition);

        return $copy;
    }

    public function rightJoin(Table $join, string $condition): self
    {
        $this->assertCanJoin();

        $copy = clone $this;
        $copy->builder = $copy->builder->rightJoin($this->from->alias(), $join->table(), $join->alias(), $condition);
        $copy->registerFieldSelectsForTable($join);

        return $copy;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string|ParameterType|Type|ArrayParameterType> $types
     */
    public function where(string $condition, array $params = [], array $types = []): self
    {
        $copy = clone $this;
        $copy->builder->andWhere($condition);
        foreach ($params as $key => $value) {
            $copy->builder = $copy->builder->setParameter($key, $value, $types[$key] ?? $this->paramType($value));
        }

        return $copy;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string|ParameterType|Type|ArrayParameterType> $types
     */
    public function having(string $condition, array $params = [], array $types = []): self
    {
        $copy = clone $this;
        $copy->builder->andHaving($condition);
        foreach ($params as $key => $value) {
            $copy->builder = $copy->builder->setParameter($key, $value, $types[$key] ?? $this->paramType($value));
        }

        return $copy;
    }

    public function select(string ...$columns): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->select(
            ...array_map(
                function (string $field): string {
                    return $this->fieldSelect[$field] ?? $field;
                },
                $columns
            )
        );

        return $copy;
    }

    public function fetchColumn(int $index = 0): false|string|null
    {
        $statement = (clone $this->builder)->setMaxResults(1)->executeQuery();

        if ($index > 0) {
            $row = $statement->fetchNumeric();

            if ($row === false) {
                return false;
            }

            return StringUtil::toString($row[$index]);
        }

        $value = $statement->fetchOne();

        if ($value === false || $value === null) {
            return $value;
        }

        return StringUtil::toString($value);
    }

    public function fetchDate(int $index = 0): ?DateTimeImmutable
    {
        $date = $this->fetchColumn($index);

        return is_string($date) ? new DateTimeImmutable($date) : null;
    }

    /**
     * @return array<array<string, string|int|float|bool|null>>
     */
    public function fetchAll(): array
    {
        $statement = (clone $this->builder)->executeQuery();

        /** @phpstan-ignore-next-line */
        return $statement->fetchAllAssociative();
    }

    /** @return array<string, string|int|float|bool|null>|null */
    public function fetch(): ?array
    {
        $statement = (clone $this->builder)->executeQuery();
        $result = $statement->fetchAssociative();

        /** @phpstan-ignore-next-line */
        return $result !== false ? $result : null;
    }

    /** @return ArrayValue<array<string, string|int|float|bool|null>> */
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
        $copy->builder = $copy->builder->setFirstResult($offset);

        if ($limit !== null) {
            $copy->builder = $copy->builder->setMaxResults($limit);
        }

        return $copy;
    }

    public function randomOrder(): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->addOrderBy('RAND()');

        return $copy;
    }

    public function orderBy(string $field, string $direction): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->addOrderBy($this->sortMap[$field] ?? $field, $direction);

        return $copy;
    }

    public function resetOrderBy(): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->resetOrderBy();

        return $copy;
    }

    public function withParameter(string $key, mixed $value, string|ParameterType|Type|ArrayParameterType $type = null): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->setParameter($key, $value, $type ?? $this->paramType($value));

        return $copy;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string|int, string|ParameterType|Type|ArrayParameterType> $types
     */
    public function withParameters(array $params = [], array $types = []): self
    {
        $copy = $this;
        $key = 0;

        foreach ($params as $name => $param) {
            $copy = $copy->withParameter($name, $param, $types[$name] ?? $types[(string)($key++)] ?? null);
        }

        return $copy;
    }

    public function groupBy(string $groupBy): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->addGroupBy($groupBy);

        return $copy;
    }

    public function resetGroupBy(): self
    {
        $copy = clone $this;
        $copy->builder = $copy->builder->resetGroupBy();

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

    private function paramType(mixed $object): string|ParameterType|Type|ArrayParameterType
    {
        if (is_array($object)) {
            return ArrayParameterType::STRING;
        }

        if (!is_object($object)) {
            return ParameterType::STRING;
        }

        return $this->types[get_class($object)] ?? ParameterType::STRING;
    }

    private function assertCanJoin(): void
    {
        if (!isset($this->from)) {
            throw new RuntimeException('FROM must be declared before JOIN');
        }
    }

    private function registerFieldSelectsForTable(Table $table): void
    {
        foreach ($table->fields() as $field) {
            $this->fieldSelect[$table->fieldPath($field)] = $table->selectField($field);
        }
    }
}
