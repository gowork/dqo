<?php

namespace GW\DatabaseAccessGenerator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateTables extends Command
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

        parent::__construct('gw:generate-tables');
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::IS_ARRAY);
        $this->addOption('path', 'p', InputOption::VALUE_OPTIONAL, '', '../gowork-bundle/src/Database/');
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);

        $filterTables = $input->getArgument('table');
        $path = $input->getOption('path');
        $overwrite = $input->getOption('overwrite');

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

        $save = function (string $content, string $fileName) use ($output, $path, $overwrite): void {
            if (file_exists($fileName)) {
                if (!$overwrite) {
                    $output->error("{$fileName} exists and not be overwrite");

                    return;
                }

                $output->note("{$fileName} exists and was overwritten");
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
}
