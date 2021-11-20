<?php declare(strict_types=1);

namespace GW\DQO\Getter;

use ArrayAccess;
use GW\DQO\Table;

final class ArrayRow implements Row
{
    /** @var array<string, mixed>|ArrayAccess<string, mixed> */
    private $row;
    private Table $table;

    /** @param array<string, mixed>|ArrayAccess<string, mixed> $row */
    public function __construct($row, Table $table)
    {
        $this->row = $row;
        $this->table = $table;
    }

    /** @return bool|float|int|string|null */
    public function get(string $field)
    {
        return $this->row[$this->table->fieldAlias($field)] ?? null;
    }
}
