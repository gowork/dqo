<?php

namespace GW\DQO;

use Doctrine\DBAL\Schema\Column as DbalColumn;
use Doctrine\DBAL\Schema\Table as DbalTable;
use GW\Value\Wrap;

final class TableFactory
{
    public function buildFromDbalTable(DbalTable $dbalTable): Table
    {
        $columns = Wrap::array($dbalTable->getColumns())
            ->map(
                function (DbalColumn $dbalColumn): Column {
                    return new Column(
                        $dbalColumn->getName(),
                        $this->camelize($dbalColumn->getName()),
                        $dbalColumn->getName(),
                        $this->type($dbalColumn),
                        !$dbalColumn->getNotnull()
                    );
                }
            );

        return new Table(ucfirst($this->camelize($dbalTable->getName())), ...$columns);
    }

    private function type(DbalColumn $dbalColumn): string
    {
        $type = $dbalColumn->getType();

        if (preg_match('#\(DC2Type:(.+?)\)#i', $dbalColumn->getComment(), $matches)) {
            $type = $matches[1];
        }

        return $type->getName();
    }

    private function camelize(string $value): string
    {
        return Wrap::stringsArray(explode('_', $value))
            ->upperFirst()
            ->implode('')
            ->lowerFirst()
            ->toString();
    }
}
