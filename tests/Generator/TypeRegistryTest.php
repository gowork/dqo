<?php declare(strict_types=1);

namespace Generator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use GW\DQO\Generator\TypeRegistry;
use PHPUnit\Framework\TestCase;

final class TypeRegistryTest extends TestCase
{
    function test_getting_builtin_type()
    {
        $this->registerType(NonNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isClass());
        self::assertEquals('string', $type->phpType());
        self::assertFalse($type->allowsNull());
    }

    function test_getting_builtin_type_nullable()
    {
        $this->registerType(NullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isClass());
        self::assertEquals('string', $type->phpType());
        self::assertTrue($type->allowsNull());
    }

    function test_getting_builtin_type_from_doc_block()
    {
        $this->registerType(DocBlockNonNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isClass());
        self::assertEquals('string', $type->phpType());
        self::assertFalse($type->allowsNull());
    }

    function test_getting_builtin_type_nullable_from_doc_block()
    {
        $this->registerType(DocBlockNullableString::class);

        $registry = new TypeRegistry();
        $type = $registry->type('foo');

        self::assertFalse($type->isClass());
        self::assertEquals('string', $type->phpType());
        self::assertTrue($type->allowsNull());
    }

    private function registerType(string $class, string $name = 'foo'): void
    {
        if (Type::hasType($name)) {
            Type::overrideType($name, $class);
        } else {
            Type::addType($name, $class);
        }
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
