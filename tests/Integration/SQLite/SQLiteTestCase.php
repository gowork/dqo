<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\SQLite;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Roave\BetterReflection\Reflection\ReflectionClass;
use tests\GW\DQO\Integration\IntegrationTestCase;

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
        return ReflectionClass::createFromInstance($this->conn()->getDatabasePlatform())->getShortName();
    }

    protected function conn(): Connection
    {
        return $this->conn;
    }
}
