<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\MySQL;

use GW\DQO\Generator\GenerateTables;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\TableFactory;

final class MySQLTest extends MySQLTestCase
{
    function test_real_db()
    {
        $this->dropTable('message');

        $this->executeQuery(
            <<<SQL
                CREATE TABLE message (id INTEGER PRIMARY KEY NOT NULL, 
                                      tiny_bool TINYINT NOT NULL, 
                                      tiny_int TINYINT NOT NULL COMMENT '(DC2Type:integer)', 
                                      title TEXT NULL, 
                                      title_not_null TEXT NOT NULL, 
                                      boo TEXT COMMENT '(DC2Type:BooId)',
                                      boo_not_null TEXT NOT NULL COMMENT '(DC2Type:BooId)',
                                      message TEXT)
                SQL
        );

        $path = '/tmp/';

        $generateTables = new GenerateTables(
            $this->conn(),
            new TableFactory(),
            new Renderer('tests\GW\DQO\Integration\Cases\MySQL')
        );
        $generateTables->generateClientRow($path);
        $generateTables->generate(['message'], $path, true);

        self::assertClientRow('MySQL', $this->platform());
        self::assertTable('MySQL','message');
    }
}
