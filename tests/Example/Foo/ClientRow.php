<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\Foo;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use GW\DQO\TableRow;

abstract class ClientRow extends TableRow
{
    protected static function getPlatform(): AbstractPlatform
    {
        static $platform;

        return $platform ?? $platform = new MySQLPlatform();
    }
}
