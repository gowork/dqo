<?php declare(strict_types=1);

namespace tests\GW\DQO\Usage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class UsageTest extends TestCase
{
    private const SYMFONY_DIR = __DIR__ . '/symfony/';

    private function runOnSymfonyDIr(array $command): void
    {
        (new Process($command,  self::SYMFONY_DIR))->run();
    }

    private function mustRunOnSymfonyDIr(array $command): void
    {
        (new Process($command,  self::SYMFONY_DIR))->mustRun();
    }

    function test_real_app()
    {
        $this->runOnSymfonyDIr(['rm', 'composer.lock']);
        $this->runOnSymfonyDIr(['rm', 'src/ClientRow.php']);
        $this->runOnSymfonyDIr(['rm', 'src/UserTable.php']);
        $this->runOnSymfonyDIr(['rm', 'src/UserRow.php']);
        $this->runOnSymfonyDIr(['rm', '-rf', 'vendor', 'repo']);
        $this->mustRunOnSymfonyDIr(['composer', 'install']);
        $this->mustRunOnSymfonyDIr(['bin/console']);
        $this->mustRunOnSymfonyDIr(['bin/console', 'gw:generate-tables', 'src', 'App', 'user']);
        self::assertFileExists(self::SYMFONY_DIR . 'src/ClientRow.php');
        self::assertFileExists(self::SYMFONY_DIR . 'src/UserTable.php');
        self::assertFileExists(self::SYMFONY_DIR . 'src/UserRow.php');
    }
}
