<?php declare(strict_types=1);

namespace GW\DQO\Formatter;

use Symfony\Component\Process\Process;
use function file_exists;

final class PhpcbFormatter implements Formatter
{
    public function formatFile(string $filename): void
    {
        $bin = $this->detectBin();

        if ($bin === null) {
            return;
        }

        $process = new Process([$bin, $filename]);
        $process->mustRun();
    }

    private function detectBin(): ?string
    {
        $vendorBin = __DIR__ . '/../../../bin/phpcbf';

        if (file_exists($vendorBin)) {
            return $vendorBin;
        }

        $process = new Process(['which', 'phpcbf']);
        if ($process->run() === 0) {
            return 'phpcbf';
        }

        return null;
    }
}
