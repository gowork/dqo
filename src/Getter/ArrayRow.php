<?php declare(strict_types=1);

namespace GW\DQO\Getter;

use ArrayAccess;
use GW\DQO\Table;

final readonly class ArrayRow implements Row
{
    /** @param array<string, float|bool|int|string|null>|ArrayAccess<string, float|bool|int|string|null> $row */
    public function __construct(
        private array|ArrayAccess $row,
        private Table $table,
    ) {
    }

    public function get(string $field): float|bool|int|string|null
    {
        return $this->row[$this->table->fieldAlias($field)] ?? null;
    }
}
