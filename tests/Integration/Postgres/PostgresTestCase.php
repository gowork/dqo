<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\Postgres;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Roave\BetterReflection\Reflection\ReflectionClass;
use tests\GW\DQO\Integration\IntegrationTestCase;
use function getenv;
use function sprintf;

abstract class PostgresTestCase extends IntegrationTestCase
{
    /** @var Connection */
    private $conn;

    protected function setUp(): void
    {
        $this->conn = DriverManager::getConnection(
            [
                'url' => sprintf(
                    'pgsql://%s:%s@%s/%s',
                    getenv('POSTGRES_USER'),
                    getenv('POSTGRES_PASSWORD'),
                    getenv('POSTGRES_HOST'),
                    getenv('POSTGRES_DATABASE'),
                ),
            ],
            new Configuration(),
        );
    }

    protected function executeQuery(string $query): void
    {
        $this->conn->executeQuery($query);
    }

    protected function dropTable(string $name): void
    {
        $this->executeQuery(sprintf('DROP TABLE IF EXISTS  %s ;', $name));
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
