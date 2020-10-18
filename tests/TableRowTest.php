<?php

namespace tests\GW\DQO;

use DateTimeImmutable;
use GW\Value\Wrap;
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

    function test_datetime_immutable()
    {
        $date = '2000-01-01 12:00:01';
        $row1 = $this->buildRow([RichTable::DATETIME => $date]);
        self::assertEquals(new DateTimeImmutable($date), $row1->datetime());

        $row2 = $this->buildRow([RichTable::DATETIME => null]);
        self::assertNull($row2->datetimeOrNull());
    }

    function test_int()
    {
        $row1 = $this->buildRow([RichTable::INT => 123]);
        self::assertEquals(123, $row1->int());
        self::assertEquals(123, $row1->intOrNull());

        $row2 = $this->buildRow([RichTable::INT => null]);
        self::assertEquals(0, $row2->int());
        self::assertNull($row2->intOrNull());

        $row3 = $this->buildRow([]);
        self::assertEquals(0, $row3->int());
        self::assertNull($row3->intOrNull());
    }

    function test_bool()
    {
        $row1 = $this->buildRow([RichTable::INT => 1]);
        self::assertTrue($row1->bool());
        self::assertTrue($row1->boolOrNull());

        $row2 = $this->buildRow([RichTable::INT => 0]);
        self::assertFalse($row2->bool());
        self::assertFalse($row2->boolOrNull());

        $row3 = $this->buildRow([]);
        self::assertFalse($row3->bool());
        self::assertNull($row3->boolOrNull());

        $row4 = $this->buildRow([RichTable::INT => null]);
        self::assertFalse($row4->bool());
        self::assertNull($row4->boolOrNull());
    }

    function test_get_through_factory()
    {
        $row1 = $this->buildRow([RichTable::INT => 12]);
        self::assertEquals(1200, $row1->boolOrNull());

        $row2 = $this->buildRow([RichTable::INT => null]);
        self::assertNull($row2->boolOrNull());

        $row3 = $this->buildRow([RichTable::INT => null]);
        self::assertNull($row3->boolOrNull());
    }

    function test_get_through_doctrine_type()
    {
        $row1 = $this->buildRow([RichTable::STRING => '{"message":"Hello World"}']);
        self::assertEquals(['message' => 'Hello World'], $row1->jsonOrNull());

        $row2 = $this->buildRow([RichTable::STRING => null]);
        self::assertNull($row2->jsonOrNull());

        $row3 = $this->buildRow([]);
        self::assertNull($row3->jsonOrNull());
    }

    public function rawData(): array
    {
        return [
            [RichTable::INT, 123],
            [RichTable::STRING, 'John'],
            [RichTable::DATETIME, '2000-01-01 12:00:01'],
        ];
    }

    private function buildRow(array $data): RichRow
    {
        return new RichRow(
            Wrap::assocArray($data)
                ->mapKeys(fn(string $key): string => $this->table->fieldAlias($key))
                ->toAssocArray(),
            $this->table
        );
    }
}
