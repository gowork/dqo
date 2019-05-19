<?php declare(strict_types=1);

namespace tests\GW\DQO\SQLite\Cases\One;

final class MessageRow extends ClientRow
{
    public function id(): int
    {
        return $this->getInt(MessageTable::ID);
    }

    public function title(): string
    {
        return $this->getString(MessageTable::TITLE);
    }

    public function message(): ?string
    {
        return $this->getString(MessageTable::MESSAGE);
    }
}
