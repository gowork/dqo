<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\Cases\SQLite;

use GW\DQO\Table;

final class MessageTable extends Table
{
    public const ID = 'id';
    public const TITLE = 'title';
    public const MESSAGE = 'message';

    public function id(): string
    {
        return $this->fieldPath(self::ID);
    }

    public function title(): string
    {
        return $this->fieldPath(self::TITLE);
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
