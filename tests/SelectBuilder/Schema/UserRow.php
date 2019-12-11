<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder\Schema;

final class UserRow extends ClientRow
{
    public function id(): int
    {
        return $this->getInt(UserTable::ID);
    }

    public function name(): string
    {
        return $this->getString(UserTable::NAME);
    }
}
