<?php declare(strict_types=1);

namespace GW\DQO\Formatter;

use Symfony\Component\Process\Process;
use function file_exists;

final class CsFixerFormatter implements Formatter
{
    public function formatFile(string $filename): void
    {
        $bin = $this->detectBin();

        if ($bin === null) {
            return;
        }

        $process = new Process([$bin, 'fix', $filename]);
        $process->mustRun();
    }

    private function detectBin(): ?string
    {
        $vendorBin = __DIR__ . '/../../../bin/php-cs-fixer';

        if (file_exists($vendorBin)) {
            return $vendorBin;
        }

        $process = new Process(['which', 'php-cs-fixer']);
        if ($process->run() === 0) {
            return 'php-cs-fixer';
        }

        return null;
    }
}
