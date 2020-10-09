<?php declare(strict_types=1);

namespace GW\DQO\Generator\Render;

use GW\DQO\Generator\ClassInfo;
use function array_merge;
use function implode;
use function in_array;
use function sort;

final class ClassHead implements Line
{
    private string $namespace;
    private string $header;
    /** @var string[] */
    private array $uses;

    /** @param string[] $uses */
    public function __construct(string $namespace, array $uses = [], string $header = '')
    {
        $this->namespace = $namespace;
        $this->uses = $uses;
        $this->header = $header;
    }

    public function useClass(ClassInfo $class): self
    {
        if ($class->namespace() === $this->namespace) {
            return $this;
        }

        $use = "use {$class->fullName()};";
        if (in_array($use, $this->uses, true)) {
            return $this;
        }

        $clone = clone $this;
        $clone->uses[] = $use;
        sort($clone->uses);

        return $clone;
    }

    public function render(): string
    {
        $uses = [];

        if ($this->namespace !== '') {
            $uses[] = "namespace {$this->namespace};";
        }

        if ($this->uses) {
            $uses[] = '';
            $uses = array_merge($uses, $this->uses);
        }

        $usesString = implode("\n", $uses);

        return "<?php declare(strict_types=1);\n\n{$usesString}\n\n{$this->header}";
    }
}
