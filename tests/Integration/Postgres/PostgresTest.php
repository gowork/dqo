<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\Postgres;

use GW\DQO\Generator\GenerateTables;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\TableFactory;

final class PostgresTest extends PostgresTestCase
{
    function test_real_db()
    {
        $this->dropTable('message');

        $this->executeQuery(
            <<<SQL
            CREATE TABLE message (id INTEGER PRIMARY KEY NOT NULL, 
                                  title TEXT NULL, 
                                  title_not_null TEXT NOT NULL, 
                                  boo TEXT,
                                  boo_not_null TEXT NOT NULL,
                                  message TEXT)
            SQL
        );

        $this->executeQuery("COMMENT ON COLUMN message.boo IS '(DC2Type:BooId)'");
        $this->executeQuery("COMMENT ON COLUMN message.boo_not_null IS '(DC2Type:BooId)'");

        $path = '/tmp/';

        $generateTables = new GenerateTables(
            $this->conn(),
            new TableFactory(),
            new Renderer('tests\GW\DQO\Integration\Cases\One')
        );
        $generateTables->generateClientRow($path);
        $generateTables->generate(['message'], $path, true);

        self::assertClientRow('Postgres', $this->platform());
        self::assertTable('Postgres', 'message');
    }
}
