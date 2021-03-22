<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use GW\DQO\Generator\Render\Block;
use GW\DQO\Generator\Render\Body;
use GW\DQO\Generator\Render\ClassHead;
use OpenSerializer\Type\TypeInfo;
use PhpParser\Builder\Use_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\PrettyPrinter\Standard;
use function array_map;
use function get_class;
use function in_array;
use function sprintf;
use const PHP_EOL;

final class Renderer
{
    private const TYPE_RETURN = [
        'integer' => 'int',
        'smallint' => 'int',
        'tinyint' => 'int',
        'bigint' => 'int',
        'string' => 'string',
        'text' => 'string',
        'bool' => 'bool',
        'boolean' => 'bool',
        'datetime' => '\DateTimeImmutable',
        'datetime_immutable' => '\DateTimeImmutable',
        'DateTimeImmutable' => '\DateTimeImmutable',
    ];

    private string $namespace;
    private TypeRegistry $types;

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
        $factory = new BuilderFactory();

        $node = $factory
            ->namespace($this->namespace)
            ->addStmt($factory->use('GW\DQO\Table'))
            ->addStmt(
                $factory->class("{$table->name()}Table")
                    ->extend('Table')
                    ->makeFinal()
                    ->addStmts(
                        array_map(
                            static fn(Column $column) => new ClassConst(
                                [new Const_($column->nameConst(), new String_($column->dbName()))],
                                Class_::MODIFIER_PUBLIC
                            ),
                            $table->columns(),
                        )
                    )
                    ->addStmts(
                        array_map(
                            static fn(Column $column) => $factory->method($column->methodName())
                                ->setReturnType('string')
                                ->makePublic()
                                ->addStmt(
                                    new Return_(
                                        new MethodCall(
                                            new Variable('this'), 'fieldPath', [
                                                new Arg(new ClassConstFetch(new Name('self'), $column->nameConst())),
                                            ]
                                        )
                                    )
                                ),
                            $table->columns(),
                        )
                    )
                    ->addStmt(
                        $factory->method('createRow')
                            ->addParam($factory->param('raw')->setType('array'))
                            ->setReturnType("{$table->name()}Row")
                            ->makePublic()
                            ->addStmt(
                                new Return_(
                                    new New_(
                                        new Name("{$table->name()}Row"),
                                        [
                                            new Arg(new Variable('raw')),
                                            new Arg(new Variable('this')),
                                        ]
                                    )
                                )
                            )
                    )
            )
            ->getNode();

        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile(
            [new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]), $node]
        ) . PHP_EOL;
    }

    public function renderRowFile(Table $table): string
    {
        $factory = new BuilderFactory();
        $uses = [];

        $node = $factory
            ->namespace($this->namespace)
            ->addStmts(
                array_filter(
                    array_map(
                        function (Column $column) use ($factory, &$uses): ?Use_ {
                            $typeInfo = $this->types->type($column->type());

                            if ($typeInfo->isObject()) {
                                $fullName = $typeInfo->type();

                                if (!in_array($fullName, $uses, true)) {
                                    $uses[] = $fullName;

                                    return $factory->use($fullName);
                                }
                            }

                            return null;
                        },
                        $table->columns(),
                    )
                )
            )
            ->addStmt(
                $factory->class("{$table->name()}Row")
                    ->extend('ClientRow')
                    ->makeFinal()
                    ->addStmts(
                        array_map(
                            function (Column $column) use ($table, $factory) {
                                $typeInfo = $this->types->type($column->type());

                                return $factory->method($column->methodName())
                                    ->setReturnType($this->typeDef($column, $typeInfo))
                                    ->makePublic()
                                    ->addStmt($this->valueReturn($table, $column, $typeInfo));
                            },
                            $table->columns(),
                        )
                    )
            )
            ->getNode();

        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile(
                [new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]), $node]
            ) . PHP_EOL;
    }

    public function renderClientRow(AbstractPlatform $databasePlatform): string
    {
        $factory = new BuilderFactory();
        $classInfo = new ClassInfo(get_class($databasePlatform));

        $node = $factory
            ->namespace($this->namespace)
            ->addStmt($factory->use('GW\DQO\TableRow'))
            ->addStmt($factory->use('Doctrine\DBAL\Platforms\AbstractPlatform'))
            ->addStmt($factory->use(get_class($databasePlatform)))
            ->addStmt(
                $factory->class('ClientRow')
                    ->extend('TableRow')
                    ->makeAbstract()
                    ->addStmt(
                        $factory->method('getPlatform')
                            ->makeStatic()
                            ->makeProtected()
                            ->setReturnType('AbstractPlatform')
                            ->addStmts(
                                [
                                    new Static_([new StaticVar(new Variable('platform'))]),
                                    new Return_(
                                        new Coalesce(
                                            new Variable('platform'),
                                            new Assign(
                                                new Variable('platform'),
                                                new New_(new Name($classInfo->shortName()))
                                            )
                                        )
                                    ),
                                ]
                            )
                    )
            )
            ->getNode();

        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile(
                [new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]), $node]
            ) . PHP_EOL;
    }

    private function typeDef(Column $column, TypeInfo $type): string
    {
        $phpType = self::TYPE_RETURN[$column->type()] ?? 'string';

        if ($type->isObject()) {
            /** @phpstan-var class-string $className */
            $className = $type->type();
            $class = new ClassInfo($className);
            $phpType = $class->shortName();
        }

        return sprintf('%s%s', $column->optional() ? '?' : '', $phpType);
    }

    private function valueReturn(Table $table, Column $column, TypeInfo $type): Return_
    {
        $const = new Arg(
            new ClassConstFetch(new Name("{$table->name()}Table"), $column->nameConst())
        );

        switch ($column->type()) {
            case 'integer':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
                if ($column->optional()) {
                    return $this->returnStatement('getNullableInt', $const);
                }
                return $this->returnStatement('getInt', $const);

            case 'string':
            case 'text':
                if ($column->optional()) {
                    return $this->returnStatement('getNullableString', $const);
                }
                return $this->returnStatement('getString', $const);

            case 'datetime':
            case 'datetime_immutable':
                return $this->returnStatement('getNullableDateTimeImmutable', $const);

            case 'boolean':
                if ($column->optional()) {
                    return $this->returnStatement('getNullableBool', $const);
                }
                return $this->returnStatement('getBool', $const);
        }

        if (!$type->isObject()) {
            if ($column->optional()) {
                return $this->returnStatement('getNullableString', $const);
            }
            return $this->returnStatement('getString', $const);
        }

        return $this->returnStatement('getThroughType', new Arg(new String_($column->type())), $const);
    }

    private function returnStatement(string $methodName, Arg ...$arguments): Return_
    {
        return new Return_(
            new MethodCall(new Variable('this'), $methodName, $arguments)
        );
    }
}
