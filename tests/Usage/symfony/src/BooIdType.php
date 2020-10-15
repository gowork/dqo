<?php declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use tests\GW\DQO\Example\Foo\Boo\BooId;

final class BooIdType extends Type
{
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param mixed[] $column The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?BooId
    {
        return $value !== null ? BooId::from($value) : null;
    }

    /**
     * Gets the name of this type.
     * @todo Needed?
     */
    public function getName(): string
    {
        return 'BooId';
    }
}
