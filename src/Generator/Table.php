<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final class Table
{
    private string $name;
    /** @var Column[] */
    private array $columns;

    public function __construct(string $name, Column ...$columns)
    {
        $this->name = $name;
        $this->columns = $columns;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Column[]
     */
    public function columns(): array
    {
        return $this->columns;
    }
}
