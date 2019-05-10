<?php

namespace GW\DQO;

final class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $dbName;

    /** @var string */
    private $type;

    /** @var string */
    private $methodName;

    /** @var bool */
    private $optional;

    public function __construct(string $name, string $methodName, string $dbName, string $type, bool $optional)
    {
        $this->name = $name;
        $this->dbName = $dbName;
        $this->type = $type;
        $this->methodName = $methodName;
        $this->optional = $optional;
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
}
