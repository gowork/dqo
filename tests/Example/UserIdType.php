<?php declare(strict_types=1);

namespace tests\GW\DQO\Example;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use tests\GW\DQO\Example\Id\UserId;

final class UserIdType extends Type
{
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array<string, mixed> $column The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        return $value !== null ? UserId::from($value) : null;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     *
     * @todo Needed?
     */
    public function getName()
    {
        return 'UserId';
    }
}
