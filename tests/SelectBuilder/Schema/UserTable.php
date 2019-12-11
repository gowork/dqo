<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder\Schema;

use GW\DQO\Table;

final class UserTable extends Table
{
    public const ID = 'id';
    public const NAME = 'name';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function name(): string
    {
        return $this->fieldPath(self::NAME);
    }

    public function createRow(array $raw): UserRow
    {
        return new UserRow($raw, $this);
    }
}
