<?php declare(strict_types=1);

namespace tests\GW\DQO\Usage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function sprintf;
use function str_replace;

final class UsageTest extends TestCase
{
    private const SYMFONY_DIR = __DIR__ . '/symfony/';
    private static string $originalComposerContent;

    private function runOnSymfonyDIr(array $command): void
    {
        (new Process($command,  self::SYMFONY_DIR))->run();
    }

    private function mustRunOnSymfonyDir(array $command, array $env = []): void
    {
        (new Process($command,  self::SYMFONY_DIR, $env))->mustRun();
    }

    protected function setUp(): void
    {
        $composerFile = __DIR__ . '/../../composer.json';
        self::$originalComposerContent = file_get_contents($composerFile);
        file_put_contents(
            $composerFile,
            str_replace(
                '"name": "gowork/dqo",',
                '"name": "gowork/dqo", "version": "0.1.0", ',
                self::$originalComposerContent
            )
        );
    }

    protected function tearDown(): void
    {
        $composerFile = __DIR__ . '/../../composer.json';
        file_put_contents(
            $composerFile,
            self::$originalComposerContent,
        );
    }

    public function test_real_app()
    {
        $env = [
            'DATABASE_URL' => sprintf(
                'mysql://%s:%s@%s/%s',
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASSWORD'),
                getenv('MYSQL_HOST'),
                getenv('MYSQL_DATABASE'),
            ),
        ];

        $this->runOnSymfonyDIr(['rm', 'composer.lock']);
        $this->runOnSymfonyDIr(['rm', 'src/ClientRow.php']);
        $this->runOnSymfonyDIr(['rm', 'src/UserTable.php']);
        $this->runOnSymfonyDIr(['rm', 'src/UserRow.php']);
        $this->runOnSymfonyDIr(['rm', '-rf', 'vendor', 'repo']);
        $this->mustRunOnSymfonyDir(['composer', 'install'], $env);
        $this->mustRunOnSymfonyDir(['bin/console'], $env);
        $this->mustRunOnSymfonyDir(['bin/console', 'dqo:generate-tables', '--overwrite', 'src', 'App', 'user'], $env);
        self::assertFileExists(self::SYMFONY_DIR . 'src/ClientRow.php');
        self::assertFileExists(self::SYMFONY_DIR . 'src/UserTable.php');
        self::assertFileExists(self::SYMFONY_DIR . 'src/UserRow.php');
    }
}
