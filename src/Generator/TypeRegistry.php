<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\ObjectType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Type;
use OpenSerializer\Type\DocBlockPropertyResolver;
use OpenSerializer\Type\PropertyTypeResolvers;
use OpenSerializer\Type\TypedPropertyResolver;
use OpenSerializer\Type\TypeInfo as DqoTypeInfo;
use ReflectionClass;
use function get_class;

final class TypeRegistry
{
    /** @var array<string, string> */
    private static $_typesMap = [
        ArrayType::class => 'array',
        SimpleArrayType::class => 'array',
        JsonArrayType::class => 'array',
        JsonType::class => 'array',
        ObjectType::class => 'object',
        BooleanType::class => 'bool',
        IntegerType::class => 'int',
        SmallIntType::class => 'int',
        BigIntType::class => 'int',
        StringType::class => 'string',
        TextType::class => 'string',
        DateTimeType::class => 'string',
        DateTimeImmutableType::class => 'string',
        DateTimeTzType::class => 'string',
        DateTimeTzImmutableType::class => 'string',
        DateType::class => 'string',
        DateImmutableType::class => 'string',
        TimeType::class => 'string',
        TimeImmutableType::class => 'string',
        DecimalType::class => 'string',
        FloatType::class => 'string',
        BinaryType::class => 'string',
        BlobType::class => 'string',
        GuidType::class => 'string',
        DateIntervalType::class => 'string',
    ];

    public function type(string $name): DqoTypeInfo
    {
        $typeResolver = new PropertyTypeResolvers(
            new TypedPropertyResolver(),
            new DocBlockPropertyResolver(),
        );

        $dbalType = Type::getType($name);
        $classInfo = new ReflectionClass($dbalType);
        $methodInfo = $classInfo->getMethod('convertToPHPValue');
        $typeInfo = $typeResolver->resolveMethodType($classInfo, $methodInfo);

        if ($typeInfo->isMixed() && isset(self::$_typesMap[get_class($dbalType)])) {
            return DqoTypeInfo::ofObject(self::$_typesMap[get_class($dbalType)], true);
        }

        return $typeInfo;
    }
}
