<?php

namespace GW\DQO\Generator\Render;

final class Body implements Line
{
    /** @var string[] */
    private array $lines;

    public function __construct(string ...$lines)
    {
        $this->lines = $lines;
    }

    public function render(): string
    {
        return \rtrim(implode("\n", $this->lines));
    }
}
