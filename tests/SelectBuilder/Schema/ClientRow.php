<?php declare(strict_types=1);

namespace tests\GW\DQO\SelectBuilder\Schema;

use GW\DQO\TableRow;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL57Platform;

abstract class ClientRow extends TableRow
{
    protected static function getPlatform(): AbstractPlatform
    {
        static $platform;

        return $platform ?? $platform = new MySQL57Platform();
    }
}
