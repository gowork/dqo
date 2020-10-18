<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\TableRowTest;

use DateTimeImmutable;
use tests\GW\DQO\Example\Foo\ClientRow;

final class RichRow extends ClientRow
{
    public function int(): int
    {
        return $this->getInt(RichTable::INT);
    }

    public function intOrNull(): ?int
    {
        return $this->getNullableInt(RichTable::INT);
    }

    public function bool(): bool
    {
        return $this->getBool(RichTable::INT);
    }

    public function boolOrNull(): ?bool
    {
        return $this->getNullableBool(RichTable::INT);
    }

    public function string(): string
    {
        return $this->getString(RichTable::STRING);
    }

    public function stringOrNull(): ?string
    {
        return $this->getNullableString(RichTable::STRING);
    }

    public function datetime(): DateTimeImmutable
    {
        return $this->getDateTimeImmutable(RichTable::DATETIME);
    }

    public function datetimeOrNull(): ?DateTimeImmutable
    {
        return $this->getNullableDateTimeImmutable(RichTable::DATETIME);
    }

    public function multiplyOrNull(int $by): ?int
    {
        return $this->getThrough(static fn(int $value): int =>  $value * $by, RichTable::INT);
    }

    public function jsonOrNull(): ?array
    {
        return $this->getThroughType('json', RichTable::STRING);
    }
}
