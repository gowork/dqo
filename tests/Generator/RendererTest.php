<?php declare(strict_types=1);

namespace tests\GW\DQO;

use GW\DQO\Generator\Column;
use GW\DQO\Generator\Renderer;
use GW\DQO\Generator\Table;
use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase
{
    function test_generate()
    {
        $renderer = new Renderer('tests\GW\DQO\Example');
        $renderedContent = $renderer->renderTableFile(
            new Table(
                'User',
                new Column('id', 'id', 'id', 'string', false),
                new Column('email', 'email', 'email', 'string', false),
                new Column('name', 'name', 'name', 'string', false),
                new Column('surname', 'surname', 'surname', 'string', false),
            )
        );

        self::assertStringEqualsFile(__DIR__ . '/../Example/UserTable.php', $renderedContent);
    }
}
