<?php declare(strict_types=1);

namespace GW\DQO\Formatter;

interface Formatter
{
    public function formatFile(string $filename): void;
}
