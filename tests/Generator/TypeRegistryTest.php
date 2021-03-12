<?php declare(strict_types=1);

namespace tests\GW\DQO\Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use GW\DQO\Generator\TypeRegistry;

/**
 * @covers \GW\DQO\Generator\TypeRegistry
 */
final class TypeRegistryTest extends DoctrineTestCase
{
    function test_getting_builtin_type()
    {
        $this->registerType(NonNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isObject());
        self::assertEquals('string', $type->type());
        self::assertFalse($type->isNullable());
    }

    function test_getting_builtin_type_nullable()
    {
        $this->registerType(NullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isObject());
        self::assertEquals('string', $type->type());
        self::assertTrue($type->isNullable());
    }

    function test_getting_builtin_type_from_doc_block()
    {
        $this->registerType(DocBlockNonNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isObject());
        self::assertEquals('string', $type->type());
        self::assertFalse($type->isNullable());
    }

    function test_getting_builtin_type_nullable_from_doc_block()
    {
        $this->registerType(DocBlockNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isObject());
        self::assertEquals('string', $type->type());
        self::assertTrue($type->isNullable());
    }
}

final class NonNullableString extends Type
{
    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
        return 'test';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName(): string
    {
        return 'foo';
    }
}

final class NullableString extends Type
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return 'test';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName(): string
    {
        return 'foo';
    }
}

final class DocBlockNonNullableString extends Type
{
    /**
     * @return string
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
        return 'test';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName(): string
    {
        return 'foo';
    }
}

final class DocBlockNullableString extends Type
{
    /**
     * @return string|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return 'test';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName(): string
    {
        return 'foo';
    }
}
