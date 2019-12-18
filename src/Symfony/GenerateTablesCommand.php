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

    /** @var GenerateTables */
    private $generateTables;

    public function __construct(GenerateTables $generateTables)
    {
        parent::__construct();
        $this->generateTables = $generateTables;
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED);
        $this->addArgument('table', InputArgument::IS_ARRAY);
        $this->addArgument('namespace', InputArgument::IS_ARRAY);
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE);
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
