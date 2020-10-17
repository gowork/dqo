<?php declare(strict_types=1);

namespace GW\DQO\Formatter;

final class MultiFormatter implements Formatter
{
    /** @var Formatter[] */
    private array $formatters;

    public function __construct(Formatter ...$formatters)
    {
        $this->formatters = $formatters;
    }

    public function formatFile(string $filename): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->formatFile($filename);
        }
    }
}
