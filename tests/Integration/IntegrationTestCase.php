<?php declare(strict_types=1);

namespace tests\GW\DQO\Integration;

use tests\GW\DQO\Generator\AstAssertions;
use tests\GW\DQO\Generator\DoctrineTestCase;

abstract class IntegrationTestCase extends DoctrineTestCase
{
    use AstAssertions;

    protected static function assertTable(string $testCase, string $tableName): void
    {
        $tableName = ucfirst($tableName);

        self::assertAstFilesEquals(
            __DIR__ . '/Cases/' . $testCase . '/' . $tableName . 'Row.php',
            '/tmp/' . $tableName . 'Row.php'
        );

        self::assertAstFilesEquals(
            __DIR__ . '/Cases/' . $testCase . '/' . $tableName . 'Table.php',
            '/tmp/' . $tableName . 'Table.php'
        );
    }

    protected static function assertClientRow(string $testCase, string $platformName): void
    {
        self::assertAstEquals(
            '/tmp/ClientRow.php',
            str_replace(
                '%platform%',
                $platformName,
                file_get_contents(__DIR__ . '/Cases/' . $testCase . '/ClientRow.txt')
            ),
        );
    }
}
