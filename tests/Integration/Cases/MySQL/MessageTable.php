<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\Cases\MySQL;

use GW\DQO\Table;

final class MessageTable extends Table
{
    public const ID = 'id';
    public const TINY_BOOL = 'tiny_bool';
    public const TINY_INT = 'tiny_int';
    public const TITLE = 'title';
    public const TITLE_NOT_NULL = 'title_not_null';
    public const BOO = 'boo';
    public const BOO_NOT_NULL = 'boo_not_null';
    public const MESSAGE = 'message';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function tinyBool(): string
    {
        return $this->fieldPath(self::TINY_BOOL);
    }

    public function tinyInt(): string
    {
        return $this->fieldPath(self::TINY_INT);
    }

    public function title(): string
    {
        return $this->fieldPath(self::TITLE);
    }

    public function titleNotNull(): string
    {
        return $this->fieldPath(self::TITLE_NOT_NULL);
    }

    public function boo(): string
    {
        return $this->fieldPath(self::BOO);
    }

    public function booNotNull(): string
    {
        return $this->fieldPath(self::BOO_NOT_NULL);
    }

    public function message(): string
    {
        return $this->fieldPath(self::MESSAGE);
    }

    public function createRow(array $raw): MessageRow
    {
        return new MessageRow($raw, $this);
    }
}
