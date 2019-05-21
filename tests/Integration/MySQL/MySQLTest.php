<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\MySQL;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use GW\DQO\Generator\GenerateTables;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\TableFactory;
use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Integration\IntegrationTestCase;

final class MySQLTest extends IntegrationTestCase
{
    function test_real_db()
    {
        $conn = DriverManager::getConnection(['url' => 'mysql://test:test@mysql/test'], new Configuration());

        $conn->executeQuery(
            <<<SQL
                DROP TABLE IF EXISTS  message ;
                SQL
        );

        $conn->executeQuery(
            <<<SQL
                CREATE TABLE message (id INTEGER PRIMARY KEY NOT NULL, 
                                      title TEXT NOT NULL, 
                                      message TEXT)
                SQL
        );

        $path = '/tmp/';

        $generateTables = new GenerateTables(
            $conn,
            new TableFactory(),
            new Renderer('tests\GW\DQO\Integration\Cases\One')
        );
        $generateTables->generateClientRow($path);
        $generateTables->generate(['message'], $path, true);

        self::assertClientRow('One', 'MySQL57Platform');
        self::assertTable('One','message');
    }
}
