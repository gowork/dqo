<?php declare(strict_types=1);

namespace GW\DQO\Getter;

use ArrayAccess;
use GW\DQO\Table;

final class ArrayAccessRow implements Row
{
    /** @var ArrayAccess<string, mixed> */
    private ArrayAccess $row;
    private Table $table;

    /** @param ArrayAccess<string, mixed> $row */
    public function __construct(ArrayAccess $row, Table $table)
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
