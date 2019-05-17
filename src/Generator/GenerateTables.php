<?php

namespace GW\DQO\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;

class GenerateTables
{
    /** @var Connection */
    private $connection;

    /** @var TableFactory */
    private $tableFactory;

    /** @var Renderer */
    private $renderer;

    public function __construct(Connection $connection, TableFactory $tableFactory, Renderer $renderer)
    {
        $this->connection = $connection;
        $this->tableFactory = $tableFactory;
        $this->renderer = $renderer;
    }

    /**
     * @param string[] $filterTables
     */
    public function generate(array $filterTables, string $path, bool $overwrite): void
    {
        $models = Wrap::array($this->connection->getSchemaManager()->listTables())
            ->filter(
                function (DbalTable $table) use ($filterTables): bool {
                    return in_array($table->getName(), $filterTables, true);
                }
            )
            ->toAssocValue()
            ->map(
                function (DbalTable $table): Table {
                    return $this->tableFactory->buildFromDbalTable($table);
                }
            )
            ->mapKeys(
                function (string $key, Table $table): string {
                    return $table->name();
                }
            );

        $save = function (string $content, string $fileName) use ($path, $overwrite): void {
            if (!$overwrite && file_exists($fileName)) {
                return;
            }

            file_put_contents($path . '/' . $fileName, $content);
        };

        $models
            ->map(
                function (Table $table): string {
                    return $this->renderer->renderTableFile($table);
                }
            )
            ->mapKeys(
                function (string $key): string {
                    return "{$key}Table.php";
                }
            )
            ->each($save);

        $models
            ->map(
                function (Table $table): string {
                    return $this->renderer->renderRowFile($table);
                }
            )
            ->mapKeys(
                function (string $key): string {
                    return "{$key}Row.php";
                }
            )
            ->each($save);
    }

    public function generateClientRow(string $path): void
    {
        $content = $this->renderer->renderClientRow($this->connection->getDatabasePlatform());
        file_put_contents($path . '/ClientRow.php', $content);
    }
}
