<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\Foo;

use tests\GW\DQO\Example\Foo\Boo\BooId;

final class UserRow extends ClientRow
{
    public function id(): BooId
    {
        return $this->getThroughType('BooId', UserTable::ID);
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
