<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\SQLite;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use GW\DQO\Generator\GenerateTables;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\TableFactory;
use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Integration\IntegrationTestCase;

final class SQLiteTest extends IntegrationTestCase
{
    function test_real_db()
    {
        $conn = DriverManager::getConnection(['url' => 'sqlite:///:memory:'], new Configuration());

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

        self::assertClientRow('One', 'SqlitePlatform');
        self::assertTable('One','message');
    }
}
