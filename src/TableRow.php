<?php declare(strict_types=1);

namespace GW\DQO;

use ArrayAccess;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use GW\DQO\Getter\ArrayRow;
use GW\DQO\Getter\ObjectRow;
use GW\DQO\Getter\Row;
use function is_array;

abstract class TableRow
{
    private Row $row;
    private Table $table;

    /** @param array<string, mixed>|object $row */
    public function __construct(array|object $row, Table $table)
    {
        $this->table = $table;
        $this->row = $this->initGetter($row);
    }

    abstract protected static function getPlatform(): AbstractPlatform;

    public function get(string $field): bool|float|int|string|null
    {
        return $this->row->get($field);
    }

    protected function getNullableString(string $field): ?string
    {
        $value = $this->get($field);

        if ($value === null) {
            return null;
        }

        return (string)$value;
    }

    protected function getString(string $field): string
    {
        return (string)$this->get($field);
    }

    protected function getNullableInt(string $field): ?int
    {
        $value = $this->get($field);

        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    protected function getInt(string $field): int
    {
        return (int)$this->get($field);
    }

    protected function getFloat(string $field): float
    {
        return (float)$this->get($field);
    }

    protected function getNullableFloat(string $field): ?float
    {
        $value = $this->get($field);

        if ($value === null) {
            return null;
        }

        return (float)$value;
    }

    protected function getNullableBool(string $field): ?bool
    {
        $value = $this->get($field);

        if ($value === null) {
            return null;
        }

        return (bool)$value;
    }

    protected function getBool(string $field): bool
    {
        return (bool)$this->get($field);
    }

    protected function getDateTime(string $field): DateTime
    {
        return Util\DateTimeUtil::mutable($this->getString($field));
    }

    protected function getNullableDateTime(string $field): ?DateTime
    {
        return $this->getThrough(Util\DateTimeUtil::mutable(...), $field);
    }

    protected function getDateTimeImmutable(string $field): DateTimeImmutable
    {
        return Util\DateTimeUtil::immutable($this->getString($field));
    }

    protected function getNullableDateTimeImmutable(string $field): ?DateTimeImmutable
    {
        return $this->getThrough(Util\DateTimeUtil::immutable(...), $field);
    }

    /**
     * @template T
     * @param callable(mixed):T $factory function($value): mixed
     * @return T|null
     */
    protected function getThrough(callable $factory, string $field): mixed
    {
        $value = $this->get($field);

        return $value !== null ? $factory($value) : null;
    }

    protected function getThroughType(string $dc2Type, string $field): mixed
    {
        return Type::getType($dc2Type)->convertToPHPValue($this->getNullableString($field), static::getPlatform());
    }

    /** @param array<string, mixed>|ArrayAccess<string, mixed>|object $row */
    private function initGetter($row): Row
    {
        if (is_array($row) || $row instanceof ArrayAccess) {
            return new ArrayRow($row, $this->table);
        }

        return new ObjectRow($row, $this->table);
    }
}
