<?php

namespace GW\DQO\Generator;

use GW\DQO\Generator\Render\Block;
use GW\DQO\Generator\Render\Body;
use GW\DQO\Generator\Render\ClassHead;
use GW\DQO\Generator\Render\Line;

final class Renderer
{
    private const TYPE_RETURN = [
        'integer' => 'int',
        'smallint' => 'int',
        'tinyint' => 'int',
        'bigint' => 'int',
        'string' => 'string',
        'text' => 'string',
        'datetime' => '\DateTimeImmutable',
        'datetime_immutable' => '\DateTimeImmutable',
        'DateTimeImmutable' => '\DateTimeImmutable',
    ];

    /** @var string */
    private $namespace;

    /** @var TypeRegistry */
    private $types;

    public function __construct(string $namespace = '\\')
    {
        $this->namespace = $namespace;
        $this->types = new TypeRegistry();
    }

    public function onNamespace(string $namespace): self
    {
        return new self($namespace);
    }

    public function renderTableFile(Table $table): string
    {
        $head = new ClassHead($this->namespace, ['use GW\DQO\Table;']);
        $body =
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

        return $head->render() . $body->render();
    }

    public function renderRowFile(Table $table): string
    {
        $head = new ClassHead($this->namespace, ['use GW\DQO\TableRow;']);

        $render =
            new Block(
                "final class {$table->name()}Row extends TableRow",
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

        return $head->render() . $render->render();
    }

    private function typeDef(Column $column): string
    {
        $type = self::TYPE_RETURN[$column->type()] ?? 'string';

        return sprintf('%s%s', $column->optional() ? '?' : '', $type);
    }

    private function valueReturn(Table $table, Column $column): string
    {
        $const = "{$table->name()}Table::{$column->nameConst()}";

        switch ($column->type()) {
            case 'integer':
            case 'smallint':
            case 'bigint':
                return "return \$this->getInt({$const});";

            case 'string':
            case 'text':
                return "return \$this->getString({$const});";

            case 'datetime':
            case 'datetime_immutable':
                return "return \$this->getDateTimeImmutable({$const});";

            case 'boolean':
                return "return \$this->getBool({$const});";
        }

        $type = $this->types->type($column->type());

        if (!$type->isClass()) {
            return "return \$this->getString({$const});";
        }

        $class = new ClassInfo($type->phpType());
        $stringValue = "\$this->getString({$const})";
        $construct = "new {$class->shortName()}({$stringValue})";

        if ($class->hasPublicConstructor() && !$column->optional()) {
            return "return {$construct};";
        }

        if ($class->hasPublicConstructor() && $column->optional()) {
            return "return {$stringValue} !== null ? {$construct} : null;";
        }

        $from = $class->firstStaticFactory() ?? 'from';
        $construct = "{$class->shortName()}::{$from}({$stringValue})";

        if (!$column->optional()) {
            return "return {$construct};";
        }

        return "return {$stringValue} !== null ? {$construct} : null;";
    }
}
