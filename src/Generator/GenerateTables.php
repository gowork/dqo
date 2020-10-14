<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;
use function dirname;
use function file_exists;

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
     */
    public function generate(array $filterTables, string $path, bool $overwrite): void
    {
        $models = Wrap::array($this->connection->getSchemaManager()->listTables())
            ->filter(static fn(DbalTable $table): bool => in_array($table->getName(), $filterTables, true))
            ->toAssocValue()
            ->map(fn(DbalTable $table): Table => $this->tableFactory->buildFromDbalTable($table))
            ->mapKeys(static fn(string $key, Table $table): string => $table->name());

        $save = function (string $content, string $fileName) use ($path, $overwrite): void {
            if (!$overwrite && file_exists($fileName)) {
                return;
            }

            if (
                !file_exists(dirname($path . '/' . $fileName))
                && !mkdir($concurrentDirectory = dirname($path . '/' . $fileName), 0777, true)
                && !is_dir($concurrentDirectory)
            ) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
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
                    return $this->renderer->renderQueryFile($table);
                }
            )
            ->mapKeys(
                function (string $key): string {
                    return "Query/{$key}Query.php";
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
