<?php declare(strict_types=1);

namespace GW\DQO\Getter;

use ArrayAccess;
use GW\DQO\Table;

final class ArrayRow implements Row
{
    /** @var array<string, mixed> */
    private array $row;
    private Table $table;

    /** @param array<string, mixed> $row */
    public function __construct(array $row, Table $table)
    {
        $this->row = $row;
        $this->table = $table;
    }

    /** @return mixed */
    public function get(string $field)
    {
        return $this->row[$this->table->fieldAlias($field)] ?? null;
    }
}
