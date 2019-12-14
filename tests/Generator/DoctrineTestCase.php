<?php declare(strict_types=1);

namespace tests\GW\DQO\Generator;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

abstract class DoctrineTestCase extends TestCase
{
    protected function registerType(string $class, string $name = 'foo'): void
    {
        if (Type::hasType($name)) {
            Type::overrideType($name, $class);
        } else {
            Type::addType($name, $class);
        }
    }
}
