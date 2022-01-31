<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final class Column
{
    private string $name;
    private string $dbName;
    private string $type;
    private string $methodName;
    private bool $optional;
    private bool $primary;

    public function __construct(
        string $name,
        string $methodName,
        string $dbName,
        string $type,
        bool $optional,
        bool $primary = false,
    ) {
        $this->name = $name;
        $this->dbName = $dbName;
        $this->type = $type;
        $this->methodName = $methodName;
        $this->optional = $optional;
        $this->primary = $primary;
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
