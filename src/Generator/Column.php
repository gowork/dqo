<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final readonly class Column
{
    public function __construct(
        private string $name,
        private string $methodName,
        private string $dbName,
        private string $type,
        private bool $optional,
        private bool $primary = false,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nameConst(): string
    {
        return mb_strtoupper($this->name);
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function dbName(): string
    {
        return $this->dbName;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function optional(): bool
    {
        return $this->optional;
    }

    public function primary(): bool
    {
        return $this->primary;
    }
}
