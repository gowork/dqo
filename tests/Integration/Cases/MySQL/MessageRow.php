<?php

declare (strict_types=1);

namespace tests\GW\DQO\Integration\Cases\MySQL;

use tests\GW\DQO\Example\Foo\Boo\BooId;

final class MessageRow extends ClientRow
{
    public function id(): int
    {
        return $this->getInt(MessageTable::ID);
    }

    public function tinyBool(): bool
    {
        return $this->getBool(MessageTable::TINY_BOOL);
    }

    public function tinyInt(): int
    {
        return $this->getInt(MessageTable::TINY_INT);
    }

    public function title(): ?string
    {
        return $this->getNullableString(MessageTable::TITLE);
    }

    public function titleNotNull(): string
    {
        return $this->getString(MessageTable::TITLE_NOT_NULL);
    }

    public function boo(): ?BooId
    {
        return $this->getThroughType('BooId', MessageTable::BOO);
    }

    public function booNotNull(): BooId
    {
        return $this->getThroughType('BooId', MessageTable::BOO_NOT_NULL);
    }

    public function message(): ?string
    {
        return $this->getNullableString(MessageTable::MESSAGE);
    }
}
