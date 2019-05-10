<?php

namespace GW\DQO\Generator;

use GW\DQO\Generator\Column;
use GW\DQO\Generator\Table;
use GW\DQO\Generator\Render\Block;
use GW\DQO\Generator\Render\Body;
use GW\DQO\Generator\Render\Line;

final class Renderer
{
    private const HEADER = "/** This class is auto generated */\n";

    public function renderTableFile(Table $table): string
    {
        $render =
            new Block(
                "final class {$table->name()}Table extends Table",
                ...array_map(
                    function (Column $column): Line {
                        return new Body(
                            "public const {$column->nameConst()} = '{$column->dbName()}';"
                        );
                    },
                    $table->columns()
                ),
                ...[new Body()],
                ...array_map(
                    function (Column $column): Line {
                        return new Block(
                            "public function {$column->methodName()}(): string",
                            new Body(
                                "return \$this->fieldPath(self::{$column->nameConst()});"
                            )
                        );
                    },
                    $table->columns()
                )
            );

        $uses = 'namespace GW\DQO;';

        return "<?php \n\n{$uses}\n\n" . self::HEADER . "{$render->render()}";
    }

    public function renderRowFile(Table $table): string
    {
        $render =
            new Block(
                "final class {$table->name()}Row extends TableRow",
                ...[new Body()],
                ...array_map(
                    function (Column $column) use ($table): Line {
                        $typeDef = $this->typeDef($column);

                        return new Block(
                            "public function {$column->methodName()}(): {$typeDef}",
                            new Body($this->valueReturn($table, $column))
                        );
                    },
                    $table->columns()
                )
            );

        $uses = 'namespace GW\DQO;';

        return "<?php \n\n{$uses}\n\n" . self::HEADER . "{$render->render()}";
    }

    private function typeDef(Column $column): string
    {
        switch ($column->type()) {
            case 'integer':
            case 'smallint':
                return 'int';

            case 'string':
            case 'text':
                return 'string';

            case 'datetime':
            case 'DateTimeImmutable':
                return '\DateTimeImmutable';

            case 'boolean':
                return 'bool';
        }

        return sprintf('%s%s', $column->optional() ? '?' : '', $column->type());
    }

    private function valueReturn(Table $table, Column $column): string
    {
        $const = "{$table->name()}Table::{$column->nameConst()}";

        switch ($column->type()) {
            case 'integer':
            case 'smallint':
                return "return \$this->getInt({$const});";

            case 'string':
            case 'text':
                return "return \$this->getString({$const});";

            case 'datetime':
            case 'DateTimeImmutable':
                return "return \$this->getDateTimeImmutable({$const});";

            case 'boolean':
                return "return \$this->getBool({$const});";
        }

        if ($column->optional()) {
            return "return \$this->getThrough([{$column->type()}::class, 'from'], {$const});";
        }

        return "return {$column->type()}::from(\$this->getString({$const}));";
    }
}
