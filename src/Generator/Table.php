<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final class Table
{
    /** @var string */
    private $name;

    /** @var Column[] */
    private $columns;

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
