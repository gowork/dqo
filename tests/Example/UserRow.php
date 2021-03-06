<?php

declare (strict_types=1);

namespace tests\GW\DQO\Example;

use tests\GW\DQO\Example\Id\UserId;

final class UserRow extends ClientRow
{
    public function id(): UserId
    {
        return $this->getThroughType('UserId', UserTable::ID);
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
}
