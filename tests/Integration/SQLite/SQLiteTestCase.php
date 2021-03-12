<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\SQLite;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use GW\DQO\Generator\ClassInfo;
use tests\GW\DQO\Integration\IntegrationTestCase;
use function get_class;

abstract class SQLiteTestCase extends IntegrationTestCase
{
    /** @var Connection */
    private $conn;

    protected function setUp(): void
    {
        $this->conn = DriverManager::getConnection(['url' => 'sqlite:///:memory:'], new Configuration());
    }

    protected function executeQuery(string $query): void
    {
        $this->conn->executeQuery($query);
    }

    protected function platform(): string
    {
        return (new ClassInfo(get_class($this->conn()->getDatabasePlatform())))->shortName();
    }

    protected function conn(): Connection
    {
        return $this->conn;
    }
}
