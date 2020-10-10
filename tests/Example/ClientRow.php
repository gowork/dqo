<?php

declare (strict_types=1);
namespace tests\GW\DQO\Example;

use GW\DQO\TableRow;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
abstract class ClientRow extends TableRow
{
    protected static function getPlatform() : AbstractPlatform
    {
        static $platform;
        return $platform ?? ($platform = new MySqlPlatform());
    }
}
