<?php declare(strict_types=1);

namespace GW\DQO\Getter;

use GW\DQO\Table;

final class ObjectRow implements Row
{
    private object $row;
    private Table $table;

    public function __construct(object $row, Table $table)
    {
        $this->row = $row;
        $this->table = $table;
    }

    /** @return bool|float|int|string|null */
    public function get(string $field)
    {
        return $this->row->{$this->table->fieldAlias($field)} ?? null;
    }
}
