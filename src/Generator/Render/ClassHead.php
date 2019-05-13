<?php declare(strict_types=1);

namespace GW\DQO\Generator\Render;

use function array_merge;
use function implode;

final class ClassHead implements Line
{
    /** @var string */
    private $header;

    /** @var string[] */
    private $uses;

    public function __construct(string $namespace, array $uses = [], string $header = '')
    {
        $this->uses = array_merge(["namespace {$namespace};", ''], $uses);
        $this->header = $header;
    }

    public function render(): string
    {
        $uses = implode("\n", $this->uses);

        return "<?php declare(strict_types=1);\n\n{$uses}\n\n{$this->header}";
    }
}
