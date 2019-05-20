<?php declare(strict_types=1);

namespace GW\DQO;

use ArrayAccess;
use Closure;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use GW\DQO\Util\DateTimeUtil;
use InvalidArgumentException;
use function is_array;
use function is_object;

abstract class TableRow
{
    /** @var array|object */
    private $row;

    /** @var Closure */
    private $getter;

    /** @var Table */
    private $table;

    /**
     * @param object|array $row
     */
    public function __construct($row, Table $table)
    {
        $this->row = $row;
        $this->initGetter($row);
        $this->table = $table;
    }

    abstract protected static function getPlatform(): AbstractPlatform;

    /**
     * @return string|int|null
     */
    public function get(string $field)
    {
        return ($this->getter)($field);
    }

    protected function getNullableString(string $field): ?string
    {
        return $this->getThrough('strval', $field);
    }

    protected function getString(string $field): string
    {
        return (string)$this->get($field);
    }

    protected function getNullableInt(string $field): ?int
    {
        return $this->getThrough('intval', $field);
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
        return $this->getThrough(DateTimeUtil::mutable, $field);
    }

    protected function getDateTimeImmutable(string $field): ?DateTimeImmutable
    {
        return $this->getThrough(DateTimeUtil::immutable, $field);
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
        return Type::getType($dc2Type)->convertToPHPValue($this->getString($field), self::getPlatform());
    }

    private function initGetter($row): void
    {
        if (is_array($row) || $row instanceof ArrayAccess) {
            $this->getter = function (string $field) {
                return $this->row[$this->table->fieldAlias($field)] ?? null;
            };

            return;
        }

        if (is_object($row)) {
            $this->getter = function (string $field) {
                return $this->row->{$this->table->fieldAlias($field)} ?? null;
            };

            return;
        }

        throw new InvalidArgumentException('Unsupported database query row format.');
    }
}
