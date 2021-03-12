<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration\MySQL;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use GW\DQO\Generator\ClassInfo;
use tests\GW\DQO\Integration\IntegrationTestCase;
use function get_class;
use function getenv;
use function sprintf;

abstract class MySQLTestCase extends IntegrationTestCase
{
    /** @var Connection */
    private $conn;

    protected function setUp(): void
    {
        $this->conn = DriverManager::getConnection(
            [
                'url' => sprintf(
                    'mysql://%s:%s@%s/%s',
                    getenv('MYSQL_USER'),
                    getenv('MYSQL_PASSWORD'),
                    getenv('MYSQL_HOST'),
                    getenv('MYSQL_DATABASE'),
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
        return (new ClassInfo(get_class($this->conn()->getDatabasePlatform())))->shortName();
    }

    protected function conn(): Connection
    {
        return $this->conn;
    }
}
