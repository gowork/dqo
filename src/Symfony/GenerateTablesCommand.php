<?php declare(strict_types=1);

namespace GW\DQO\Symfony;

use GW\DQO\Formatter\Formatter;
use GW\DQO\Generator\GenerateTables;
use GW\Safe\SafeConsoleInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateTablesCommand extends Command
{
    protected static $defaultName = 'dqo:generate-tables';
    private GenerateTables $generateTables;
    private Formatter $formatter;

    public function __construct(GenerateTables $generateTables, Formatter $formatter)
    {
        parent::__construct();
        $this->generateTables = $generateTables;
        $this->formatter = $formatter;
    }

    protected function configure(): void
    {
        $this->setDescription('Generates table class for one or more database tables');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to put generated files');
        $this->addArgument('namespace', InputArgument::REQUIRED, 'Namespace for generated table class file');
        $this->addArgument('table', InputArgument::IS_ARRAY, 'Name of database table');
        $this->addOption('autofix', null, InputOption::VALUE_NONE , 'Automatically try to fix generated files formatting using phpcbf or php-cs-fixer');
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing table class when exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $arguments = SafeConsoleInput::arguments($input);
        $options = SafeConsoleInput::options($input);

        $filterTables = $arguments->strings('table');
        $path = $arguments->string('path');
        $namespace = $arguments->string('namespace');
        $overwrite = $options->bool('overwrite');

        $generateTables = $this->generateTables->onNamespace($namespace);

        $generateTables->generateClientRow($path);
        $generatedFiles = $generateTables->generate($filterTables, $path, $overwrite);

        //if ($options->bool('autofix') || $style->confirm('Use php-cs-fixer to format generated code?')) {
            foreach ($generatedFiles as $generatedFile) {
                $this->formatter->formatFile($generatedFile);
            }
        //}

        return 0;
    }
}
