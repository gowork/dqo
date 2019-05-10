<?php declare(strict_types=1);

namespace tests\GW\DQO\Example;

use GW\DQO\TableRow;

final class UserRow extends TableRow
{
    public function id(): UserId
    {
        return UserId::from($this->getString(UserTable::ID));
    }

    public function email(): string
    {
        return $this->getString(UserTable::EMAIL);
    }

    public function name(): string
    {
        return $this->getString(UserTable::NAME);
    }

    public function surname(): string
    {
        return $this->getString(UserTable::SURNAME);
    }

    public function fullName(): string
    {
        return "{$this->name()} {$this->surname()}";
    }
}
