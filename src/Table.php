<?php declare(strict_types=1);

namespace GW\DQO;

use GW\Value\Wrap;
use ReflectionClass;
use function array_values;
use function strval;

abstract class Table
{
    private string $table;
    private string $alias;
    /** @var string[] */
    private array $fields;

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

    /** @return string[] */
    public function selectAll(): array
    {
        return $this->select(...$this->fields);
    }

    /** @return string[] */
    public function select(string ...$fields): array
    {
        return Wrap::array($fields)->map($this::selectField(...))->toArray();
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

    /** @return string[] */
    public function fields(): array
    {
        return $this->fields;
    }

    public function fieldAlias(string $field): string
    {
        return "{$this->alias}_{$field}";
    }

    private function resolveTableName(): string
    {
        $class = Wrap::string(static::class);

        return $class
            // class name
            ->substring($class->positionLast('\\') ?? 0)
            ->trimLeft('\\')
            ->replacePattern('/Table$/', '')
            // snake case
            ->replacePattern('/([A-Z])/', '_$1')
            ->trimLeft('_')
            ->lower()
            ->toString();
    }

    /** @return string[] */
    private function resolveTableFields(): array
    {
        return array_map(strval(...), array_values((new ReflectionClass(static::class))->getConstants()));
    }
}
