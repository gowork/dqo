<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;

class GenerateTables
{
    private Connection $connection;
    private TableFactory $tableFactory;
    private Renderer $renderer;

    public function __construct(Connection $connection, TableFactory $tableFactory, Renderer $renderer)
    {
        $this->connection = $connection;
        $this->tableFactory = $tableFactory;
        $this->renderer = $renderer;
    }

    public function onNamespace(string $namespace): self
    {
        $clone = clone $this;
        $clone->renderer = $this->renderer->onNamespace($namespace);

        return $clone;
    }

    /**
     * @param string[] $filterTables
     * @return string[] Generated files paths
     */
    public function generate(array $filterTables, string $path, bool $overwrite): array
    {
        $models = $models = Wrap::array($filterTables)
            ->map(fn(string $tableName): DbalTable => $this->connection->getSchemaManager()->listTableDetails($tableName))
            ->filter(static fn(DbalTable $table): bool => in_array($table->getName(), $filterTables, true))
            ->toAssocValue()
            ->map(fn(DbalTable $table): Table => $this->tableFactory->buildFromDbalTable($table))
            ->mapKeys(static fn(int $key, Table $table): string => $table->name());

        /** @var string[] $generatedFiles */
        $generatedFiles = [];

        $save = function (string $content, string $fileName) use ($path, $overwrite, &$generatedFiles): void {
            $fullPath = $path . '/' . $fileName;

            if (!$overwrite && file_exists($fullPath)) {
                return;
            }

            file_put_contents($fullPath, $content);
            $generatedFiles[] = $fullPath;
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

        return $generatedFiles;
    }

    public function generateClientRow(string $path, bool $overwrite): void
    {
        if (!$overwrite && file_exists($path . '/ClientRow.php')) {
            return;
        }

        $content = $this->renderer->renderClientRow($this->connection->getDatabasePlatform());
        file_put_contents($path . '/ClientRow.php', $content);
    }
}
