<?php declare(strict_types=1);

namespace GW\DQO;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;

abstract class MysqlTableRow extends TableRow
{
    protected static function getPlatform(): AbstractPlatform
    {
        static $platform;

        return $platform ?? $platform = new MySqlPlatform();
    }
}
