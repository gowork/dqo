<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\Foo;

use GW\DQO\Table;

final class UserTable extends Table
{
    public const ID = 'id';
    public const EMAIL = 'email';
    public const NAME = 'name';
    public const SURNAME = 'surname';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function email(): string
    {
        return $this->fieldPath(self::EMAIL);
    }

    public function name(): string
    {
        return $this->fieldPath(self::NAME);
    }

    public function surname(): string
    {
        return $this->fieldPath(self::SURNAME);
    }
}
