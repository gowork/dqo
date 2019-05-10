<?php

namespace tests\GW\DQO\Generator;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\TableFactory;
use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Example\Foo\BooIdType;
use tests\GW\DQO\Example\UserIdType;

class TableFactoryTest extends TestCase
{
    function test_build_from_dbal()
    {
        Type::addType('UserId', UserIdType::class);

        $factory = new TableFactory();
        $table = $factory->buildFromDbalTable(new Table('user', [
            new Column('id', Type::getType('UserId')),
            new Column('email', Type::getType('string')),
            new Column('name', Type::getType('string')),
            new Column('surname', Type::getType('string')),
        ]));


        $renderer = new Renderer('tests\GW\DQO\Example');
        $renderedContent = $renderer->renderTableFile($table);

        self::assertStringEqualsFile(__DIR__ . '/../Example/UserTable.php', $renderedContent);
    }

    function test_build_from_dbal_with_id_in_different_namespace()
    {
        Type::addType('BooId', BooIdType::class);

        $factory = new TableFactory();
        $table = $factory->buildFromDbalTable(new Table('user', [
            new Column('id', Type::getType('BooId')),
            new Column('email', Type::getType('string')),
            new Column('name', Type::getType('string')),
            new Column('surname', Type::getType('string')),
        ]));


        $renderer = new Renderer('tests\GW\DQO\Example\Foo');
        $renderedContent = $renderer->renderRowFile($table);

        self::assertStringEqualsFile(__DIR__ . '/../Example/Foo/UserRow.php', $renderedContent);
    }
}
