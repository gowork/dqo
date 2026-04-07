<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Schema\Column as DbalColumn;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;

use function in_array;

final class TableFactory
{
    public function buildFromDbalTable(DbalTable $dbalTable): Table
    {
        $columns = Wrap::array($dbalTable->getColumns())
            ->map(
                function (DbalColumn $dbalColumn) use ($dbalTable): Column {
                    return new Column(
                        $dbalColumn->getObjectName()->toString(),
                        $this->camelize($dbalColumn->getObjectName()->toString()),
                        $dbalColumn->getObjectName()->toString(),
                        $this->type($dbalColumn),
                        !$dbalColumn->getNotnull(),
                        in_array(
                            $dbalColumn->getObjectName()->toString(),
                            $this->extractPrimaryKeyColumns($dbalTable),
                            true,
                        ),
                    );
                },
            );

        return new Table(ucfirst($this->camelize($dbalTable->getObjectName()->toString())), ...$columns);
    }

    private function type(DbalColumn $dbalColumn): string
    {
        $type = $dbalColumn->getType();

        if (preg_match('#\(DC2Type:(.+?)\)#i', $dbalColumn->getComment(), $matches) === 1) {
            return $matches[1];
        }

        return $type::getTypeRegistry()->lookupName($type);
    }

    private function camelize(string $value): string
    {
        return Wrap::stringsArray(explode('_', $value))
            ->upperFirst()
            ->implode('')
            ->lowerFirst()
            ->toString();
    }

    /**
     * @param DbalTable $dbalTable
     * @return array<string>
     */
    function extractPrimaryKeyColumns(DbalTable $dbalTable): array
    {
        return array_map(
            static fn($columnName) => $columnName->toString(),
            $dbalTable->getPrimaryKeyConstraint()?->getColumnNames() ?? [],
        );
    }
}
