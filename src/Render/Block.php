<?php

namespace GW\DQO\Render;

use GW\Value\ArrayValue;
use GW\Value\Wrap;

final class Block implements Line
{
    /** @var Line[]|ArrayValue */
    private $lines;

    /** @var string */
    private $indent = '    ';

    /** @var string */
    private $declaration;

    public function __construct(string $declaration, Line ...$lines)
    {
        $this->declaration = trim($declaration);
        $this->lines = Wrap::array($lines);
    }

    public function render(): string
    {
        $content = $this->lines
            ->flatMap(
                function (Line $block): array {
                    return explode("\n", $block->render());
                }
            )
            ->map(
                function (string $line): string {
                    return $this->indent . $line;
                }
            )
            ->implode("\n")
            ->trimRight()
        ;

        return "{$this->declaration}\n{\n{$content}\n}\n";
    }
}
