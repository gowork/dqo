<?php

namespace tests\GW\DQO;

use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Example\TableRowTest\RichRow;
use tests\GW\DQO\Example\TableRowTest\RichTable;

class TableRowTest extends TestCase
{
    private RichTable $table;

    protected function setUp(): void
    {
        $this->table = new RichTable();
    }

    /**
     * @dataProvider rawData
     */
    function test_return_raw_data_from_array(string $field, $rawValue)
    {
        $row = new RichRow([$this->table->fieldAlias($field) => $rawValue], $this->table);

        self::assertEquals($rawValue, $row->get($field));
    }

    /**
     * @dataProvider rawData
     */
    function test_return_raw_data_from_stdClass(string $field, $rawValue)
    {
        $data = new \stdClass();
        $data->{$this->table->fieldAlias($field)} = $rawValue;
        $row = new RichRow($data, $this->table);

        self::assertEquals($row->get($field), $rawValue);
    }

    public function rawData(): array
    {
        return [
            [RichTable::INT, 123],
            [RichTable::STRING, 'John'],
            [RichTable::DATETIME, '2000-01-01 12:00:01'],
        ];
    }

    public function richData(): array
    {
        return [
            [RichTable::INT, 123, 'int', 123],
            [RichTable::INT, 123, 'intOrNull', 123],
            [RichTable::INT, null, 'intOrNull', null],

            [RichTable::STRING, 'John', 'string', 'John'],
            [RichTable::STRING, 'John', 'stringOrNull', 'John'],
            [RichTable::STRING, null, 'stringOrNull', null],

            [RichTable::DATETIME, '2000-01-01 12:00:01', 'datetime', new \DateTimeImmutable('2000-01-01 12:00:01')],
            [
                RichTable::DATETIME,
                '2000-01-01 12:00:01',
                'datetimeOrNull',
                new \DateTimeImmutable('2000-01-01 12:00:01'),
            ],
        ];
    }
}
