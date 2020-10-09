<?php declare(strict_types=1);

namespace GW\DQO\Symfony;

use GW\DQO\Generator\GenerateTables;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateTablesCommand extends Command
{
    protected static $defaultName = 'gw:generate-tables';
    private GenerateTables $generateTables;

    public function __construct(GenerateTables $generateTables)
    {
        parent::__construct();
        $this->generateTables = $generateTables;
    }

    protected function configure(): void
    {
        $this->setDescription('Generates table class for one or more database tables');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to put generated files');
        $this->addArgument('namespace', InputArgument::REQUIRED, 'Namespace for generated table class file');
        $this->addArgument('table', InputArgument::IS_ARRAY, 'Name of database table');
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing table class when exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filterTables = (array)$input->getArgument('table');
        $path = (string)$input->getArgument('path');
        $namespace = (string)$input->getArgument('namespace');
        $overwrite = (bool)$input->getOption('overwrite');

        $generateTables = $this->generateTables->onNamespace($namespace);

        $generateTables->generateClientRow($path);
        $generateTables->generate($filterTables, $path, $overwrite);

        return 0;
    }
}
