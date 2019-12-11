<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder;

use GW\DQO\DatabaseSelectBuilder;
use tests\GW\DQO\Integration\MySQL\MySQLTestCase;

final class DatabaseSelectBuilderTest extends MySQLTestCase
{
    function setUp(): void
    {
        parent::setUp();

        $this->dropTable('user');
        $this->dropTable('message');

        $this->executeQuery(
            <<<SQL
                CREATE TABLE user (id INTEGER PRIMARY KEY NOT NULL, 
                                      name VARCHAR(20) NOT NULL)
                SQL
        );

        $this->executeQuery(
            <<<SQL
                CREATE TABLE message (id INTEGER PRIMARY KEY NOT NULL, 
                                      user_id INTEGER NOT NULL,
                                      message TEXT)
                SQL
        );

        $this->conn()->insert('user', ['id' => 1, 'name' => 'John']);
        $this->conn()->insert('user', ['id' => 2, 'name' => 'Marco']);
        $this->conn()->insert('message', ['id' => 1, 'user_id' => 1, 'message' => 'Hello']);
        $this->conn()->insert('message', ['id' => 2, 'user_id' => 1, 'message' => 'World']);
    }

    function test_select()
    {
        $userTable = new Schema\UserTable();
        $messageTable = new Schema\MessageTable();

        $builder = (new DatabaseSelectBuilder($this->conn()))
            ->from($messageTable)
            ->join($userTable, "{$messageTable->userId()} = {$userTable->id()}")
            ->select($userTable->name(), $messageTable->message());

        $sql = $builder->getSQL();
        $rows = $builder->fetchAll();

        self::assertEquals(
            'SELECT user.name user_name, message.message message_message FROM message message INNER JOIN user user ON message.user_id = user.id',
            $sql
        );
        self::assertCount(2, $rows);
        self::assertEquals(
            [
                ['user_name' => 'John', 'message_message' => 'Hello'],
                ['user_name' => 'John', 'message_message' => 'World'],
            ],
            $rows
        );
        self::assertEquals('John', (new Schema\UserRow($rows[0], $userTable))->name());
        self::assertEquals('Hello', (new Schema\MessageRow($rows[0], $messageTable))->message());
    }

    function test_aliases()
    {
        $userTable = new Schema\UserTable('u');
        $messageTable = new Schema\MessageTable('m');

        $builder = (new DatabaseSelectBuilder($this->conn()))
            ->from($messageTable)
            ->join($userTable, "{$messageTable->userId()} = {$userTable->id()}")
            ->select($userTable->name(), $messageTable->message());

        $sql = $builder->getSQL();
        $rows = $builder->fetchAll();

        self::assertEquals('SELECT u.name u_name, m.message m_message FROM message m INNER JOIN user u ON m.user_id = u.id', $sql);
        self::assertCount(2, $rows);
        self::assertEquals(
            [
                ['u_name' => 'John', 'm_message' => 'Hello'],
                ['u_name' => 'John', 'm_message' => 'World'],
            ],
            $rows
        );
        self::assertEquals('John', (new Schema\UserRow($rows[0], $userTable))->name());
        self::assertEquals('Hello', (new Schema\MessageRow($rows[0], $messageTable))->message());
    }
}
