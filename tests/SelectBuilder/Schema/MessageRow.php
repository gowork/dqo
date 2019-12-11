<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder\Schema;

final class MessageRow extends ClientRow
{
    public function id(): int
    {
        return $this->getInt(MessageTable::ID);
    }

    public function userId(): int
    {
        return $this->getInt(MessageTable::USER_ID);
    }

    public function message(): ?string
    {
        return $this->getNullableString(MessageTable::MESSAGE);
    }
}
