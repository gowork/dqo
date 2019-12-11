<?php

namespace GW\DQO\Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use function get_class;
use GW\DQO\Generator\Render\Block;
use GW\DQO\Generator\Render\Body;
use GW\DQO\Generator\Render\ClassHead;
use GW\DQO\Generator\Render\Line;
use function sprintf;

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

    public function __construct(string $namespace = '')
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
        $head = new ClassHead($this->namespace, []);

        $render =
            new Block(
                "final class {$table->name()}Row extends ClientRow",
                ...array_map(
                    function (Column $column) use ($table, &$head): Line {
                        $typeInfo = $this->types->type($column->type());
                        $typeDef = $this->typeDef($column, $typeInfo);

                        if ($typeInfo->isClass()) {
                            $head = $head->useClass(new ClassInfo($typeInfo->phpType()));
                        }

                        return new Block(
                            "public function {$column->methodName()}(): {$typeDef}",
                            new Body($this->valueReturn($table, $column, $typeInfo))
                        );
                    },
                    $table->columns()
                )
            );

        return $head->render() . $render->render();
    }

    public function renderClientRow(AbstractPlatform $databasePlatform): string
    {
        $head = new ClassHead(
            $this->namespace,
            [
                'use GW\DQO\TableRow;',
                'use Doctrine\DBAL\Platforms\AbstractPlatform;',
                sprintf('use %s;', get_class($databasePlatform)),
            ]
        );

        $classInfo = new ClassInfo(get_class($databasePlatform));

        $render =
            new Block(
                'abstract class ClientRow extends TableRow',
                new Block(
                    'protected static function getPlatform(): AbstractPlatform',
                    new Body(
                        'static $platform;',
                        '',
                        sprintf(
                            'return $platform ?? $platform = new %s();',
                            $classInfo->shortName()
                        )
                    )
                )
            );

        return $head->render() . $render->render();
    }

    private function typeDef(Column $column, TypeInfo $type): string
    {
        $phpType = self::TYPE_RETURN[$column->type()] ?? 'string';

        if ($type->isClass()) {
            $class = new ClassInfo($type->phpType());
            $phpType = $class->shortName();
        }

        return sprintf('%s%s', $column->optional() ? '?' : '', $phpType);
    }

    private function valueReturn(Table $table, Column $column, TypeInfo $type): string
    {
        $const = "{$table->name()}Table::{$column->nameConst()}";

        switch ($column->type()) {
            case 'integer':
            case 'smallint':
            case 'bigint':
                return $this->returnStatement('getInt', $const);

            case 'string':
            case 'text':
                if ($column->optional()) {
                    return $this->returnStatement('getNullableString', $const);
                }
                return $this->returnStatement('getString', $const);

            case 'datetime':
            case 'datetime_immutable':
                return $this->returnStatement('getDateTimeImmutable', $const);

            case 'boolean':
                return $this->returnStatement('getBool', $const);
        }

        if (!$type->isClass()) {
            if ($column->optional()) {
                return $this->returnStatement('getNullableString', $const);
            }
            return $this->returnStatement('getString', $const);
        }

        return $this->returnStatement('getThroughType', "'{$column->type()}', {$const}");
    }

    private function returnStatement(string $methodName, string $arguments): string
    {
        return "return \$this->$methodName($arguments);";
    }
}
