<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\TableRowTest;

use GW\DQO\Table;

final class RichTable extends Table
{
    public const INT = 'int';
    public const STRING = 'string';
    public const DATETIME = 'datetime';

    public function int(): string
    {
        return $this->fieldPath(self::INT);
    }

    public function string(): string
    {
        return $this->fieldPath(self::STRING);
    }

    public function datetime(): string
    {
        return $this->fieldPath(self::DATETIME);
    }
}
