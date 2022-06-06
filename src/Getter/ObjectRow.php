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

    public function get(string $field): bool|float|int|string|null
    {
        return $this->row->{$this->table->fieldAlias($field)} ?? null;
    }
}
