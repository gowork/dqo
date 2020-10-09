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
    public function __construct($row, Table $table)
    {
        $this->table = $table;
        $this->row = $this->initGetter($row);
    }

    abstract protected static function getPlatform(): AbstractPlatform;

    /**
     * @return string|int|null
     */
    public function get(string $field)
    {
        return $this->row->get($field);
    }

    protected function getNullableString(string $field): ?string
    {
        return $this->getThrough('\strval', $field);
    }

    protected function getString(string $field): string
    {
        return (string)$this->get($field);
    }

    protected function getNullableInt(string $field): ?int
    {
        return $this->getThrough('\intval', $field);
    }

    protected function getInt(string $field): int
    {
        return (int)$this->get($field);
    }

    protected function getNullableBool(string $field): ?bool
    {
        return $this->getThrough('boolval', $field);
    }

    protected function getBool(string $field): bool
    {
        return (bool)$this->get($field);
    }

    protected function getDateTime(string $field): ?DateTime
    {
        return $this->getThrough(Util\DateTimeUtil::mutable, $field);
    }

    protected function getRequiredDateTimeImmutable(string $field): DateTimeImmutable
    {
        return Util\DateTimeUtil::immutable($this->getString($field));
    }

    protected function getDateTimeImmutable(string $field): ?DateTimeImmutable
    {
        return $this->getThrough(Util\DateTimeUtil::immutable, $field);
    }

    /**
     * @param callable $factory function($value): mixed
     * @return mixed|null
     */
    protected function getThrough(callable $factory, string $field)
    {
        $value = $this->get($field);

        return $value !== null ? $factory($value) : null;
    }

    /**
     * @return mixed
     */
    protected function getThroughType(string $dc2Type, string $field)
    {
        return Type::getType($dc2Type)->convertToPHPValue($this->getString($field), static::getPlatform());
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
