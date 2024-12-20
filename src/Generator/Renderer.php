<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use GW\DQO\DatabaseSelectBuilder;
use GW\DQO\Query\AbstractDatabaseQuery;
use GW\DQO\Query\RowIterator;
use GW\Value\Wrap;
use OpenSerializer\Type\TypeInfo;
use PhpParser\Builder\Use_;
use PhpParser\BuilderFactory;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Const_;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\StaticVar;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\PrettyPrinter\Standard;

use function array_map;
use function array_push;
use function array_unique;
use function get_class;
use function in_array;
use function sprintf;
use function ucfirst;

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
                                Modifiers::PUBLIC
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
            [new Declare_([new DeclareItem('strict_types', new LNumber(1))]), $node]
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
                [new Declare_([new DeclareItem('strict_types', new LNumber(1))]), $node]
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
                [new Declare_([new DeclareItem('strict_types', new LNumber(1))]), $node]
            ) . PHP_EOL;
    }

    public function renderQueryFile(Table $table): string
    {
        $factory = new BuilderFactory();
        /** @var Column|null $idColumn */
        $idColumn = Wrap::array($table->columns())->find(fn(Column $column): bool => $column->primary());
        $methods = [];
        $uses = [];

        if ($idColumn !== null) {
            $methods[] = $factory->method('single')
                ->addParam($factory->param($idColumn->methodName())->setType($this->typeDef($idColumn, $this->types->type($idColumn->type()), $uses)))
                ->setReturnType("?{$table->name()}Row")
                ->makePublic()
                ->addStmt(
                    new Return_(
                        new MethodCall(
                            new MethodCall(new Variable('this'), "with" . ucfirst($idColumn->methodName()), [
                                new Arg(new Variable($idColumn->methodName())),
                            ]),
                            'first',
                        ),
                    )
                );
        }

        array_push($methods, ...array_map(
            function (Column $column) use ($factory) {
                $typeInfo = $this->types->type($column->type());

                return $factory->method("with" . ucfirst($column->methodName()))
                    ->setReturnType('self')
                    ->addParam(
                        new Param(
                            $factory->var($column->methodName()),
                            null,
                            $this->typeDef($column, $typeInfo),
                        )
                    )
                    ->makePublic()
                    ->addStmt(
                        new Return_(
                            new MethodCall(
                                new Variable('this'),
                                'where',
                                $factory->args([
                                    new Encapsed(
                                        [
                                            new MethodCall(
                                                new PropertyFetch(new Variable('this'), 'table'),
                                                $column->methodName(),
                                            ),
                                            new EncapsedStringPart(' = :' . $column->methodName()),
                                        ]
                                    ),
                                    new Array_(
                                        [new ArrayItem($factory->var($column->methodName()), $factory->val($column->methodName()))],
                                        ['kind' => Array_::KIND_SHORT],
                                    ),
                                ]),
                            )
                        )
                    );
            },
            $table->columns(),
        ));

        $node = $factory
            ->namespace($this->namespace . '\Query')
            ->addStmt($factory->use(AbstractDatabaseQuery::class))
            ->addStmt($factory->use(DatabaseSelectBuilder::class))
            ->addStmt($factory->use(RowIterator::class))
            ->addStmt($factory->use($this->namespace . "\\{$table->name()}Table"))
            ->addStmt($factory->use($this->namespace . "\\{$table->name()}Row"))
            ->addStmts(array_map(fn(string $className) => $factory->use($className), array_unique($uses)))
            ->addStmt(
                $factory->class("{$table->name()}Query")
                    ->extend('AbstractDatabaseQuery')
                    ->makeFinal()
                    ->addStmt(
                        $factory->property('table')
                            ->setType("{$table->name()}Table")
                            ->makePrivate()
                    )
                    ->addStmt(
                        $factory->method('__construct')
                            ->addParam($factory->param('builder')->setType('DatabaseSelectBuilder'))
                            ->makePublic()
                            ->addStmt(
                                new Assign(
                                    new PropertyFetch(new Variable('this'), 'table'),
                                    new New_(new Name("{$table->name()}Table")),
                                )
                            )
                            ->addStmt(
                                new StaticCall(
                                    new Name('parent'),
                                    '__construct',
                                    [
                                        new Arg(new Variable('builder')),
                                        new Arg(new PropertyFetch(new Variable('this'), 'table')),
                                    ]
                                )
                            )
                    )
                    ->addStmt(
                        $factory->method('table')
                            ->setReturnType("{$table->name()}Table")
                            ->makePublic()
                            ->addStmt(
                                new Return_(
                                    new PropertyFetch(new Variable('this'), 'table'),
                                )
                            )
                    )
                    ->addStmt(
                        $factory->method('all')
                            ->addParam($factory->param('fields')->setType('string')->makeVariadic())
                            ->setReturnType('iterable')
                            ->setDocComment("/** @return iterable<{$table->name()}Row> */")
                            ->makePublic()
                            ->addStmt(
                                new Assign(
                                    new Variable('builder'),
                                    new MethodCall(
                                        new MethodCall(
                                            new Variable('this'),
                                            'builder',
                                        ),
                                        'select',
                                        [
                                            new Arg(
                                                new Ternary(
                                                    new Variable('fields'),
                                                    new MethodCall(
                                                        new PropertyFetch(new Variable('this'), 'table'),
                                                        'select',
                                                        [new Arg(new Variable('fields'), unpack: true)],
                                                    ),
                                                    new MethodCall(
                                                        new PropertyFetch(new Variable('this'), 'table'),
                                                        'selectAll',
                                                    ),
                                                ),
                                                unpack: true,
                                            )
                                        ]
                                    ),
                                )
                            )
                            ->addStmt(
                                new Return_(
                                    new New_(new Name("RowIterator"), [
                                        new Arg(new Variable('builder')),
                                        new Arg(new ArrowFunction([
                                            'params' => [
                                                $factory->param('raw')->setType('array')->getNode(),
                                            ],
                                            'returnType' => new Name("{$table->name()}Row"),
                                            'expr' => new MethodCall(
                                                new PropertyFetch(new Variable('this'), 'table'),
                                                'createRow',
                                                [new Arg(new Variable('raw'))],
                                            ),
                                        ])),
                                    ]),
                                )
                            )
                    )
                    ->addStmt(
                        $factory->method('first')
                            ->setReturnType("?{$table->name()}Row")
                            ->makePublic()
                            ->addStmt(
                                new Return_(
                                    new Coalesce(
                                        new ArrayDimFetch(
                                            new Array_([
                                                new ArrayItem(new MethodCall(
                                                    new MethodCall(new Variable('this'), 'offsetLimit', [
                                                        new Arg(new LNumber(0)),
                                                        new Arg(new LNumber(1)),
                                                    ]),
                                                    'all',
                                                ), unpack: true),
                                            ], ['kind' => Array_::KIND_SHORT]),
                                            new LNumber(0),
                                        ),
                                        new ConstFetch(new Name('null')),
                                    ),
                                )
                            )
                    )
                    ->addStmts($methods)
            )
            ->getNode();

        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile(
                [new Declare_([new DeclareItem('strict_types', new LNumber(1))]), $node]
            ) . PHP_EOL;
    }

    /** @param string[] $uses */
    private function typeDef(Column $column, TypeInfo $type, array &$uses = []): Name
    {
        $phpType = self::TYPE_RETURN[$column->type()] ?? 'string';

        if ($type->isObject()) {
            /** @phpstan-var class-string $className */
            $className = $type->type();
            $class = new ClassInfo($className);
            $phpType = $class->shortName();
            $uses[] = $class->fullName();
        }

        return new Name(sprintf('%s%s', $column->optional() ? '?' : '', $phpType));
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
