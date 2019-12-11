<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder\Schema;

use GW\DQO\Table;

final class MessageTable extends Table
{
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const MESSAGE = 'message';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function userId(): string
    {
        return $this->fieldPath(self::USER_ID);
    }

    public function message(): string
    {
        return $this->fieldPath(self::MESSAGE);
    }
}
