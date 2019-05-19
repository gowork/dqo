<?php declare(strict_types=1);

namespace tests\GW\DQO\SQLite\Cases\One;

use GW\DQO\TableRow;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;

abstract class ClientRow extends TableRow
{
    protected static function getPlatform(): AbstractPlatform
    {
        static $platform;

        return $platform ?? $platform = new SqlitePlatform();
    }
}
