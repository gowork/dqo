<?php

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
        $this->addArgument('table', InputArgument::IS_ARRAY);
        $this->addArgument('path', 'p', InputArgument::REQUIRED);
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filterTables = (array)$input->getArgument('table');
        $path = (string)$input->getArgument('path');
        $overwrite = (bool)$input->getOption('overwrite');

        $this->generateTables->generateClientRow($path);
        $this->generateTables->generate($filterTables, $path, $overwrite);

        return 0;
    }
}
