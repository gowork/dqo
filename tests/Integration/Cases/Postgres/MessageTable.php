<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\Cases\Postgres;

use GW\DQO\Table;

final class MessageTable extends Table
{
    public const ID = 'id';
    public const TITLE = 'title';
    public const TITLE_NOT_NULL = 'title_not_null';
    public const BOO = 'boo';
    public const BOO_NOT_NULL = 'boo_not_null';
    public const MESSAGE = 'message';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
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
}
