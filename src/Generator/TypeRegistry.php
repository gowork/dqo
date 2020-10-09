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
use phpDocumentor\Reflection\Types as Types;
use Roave\BetterReflection\BetterReflection;

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

    public function type(string $name): TypeInfo
    {
        $dbalType = Type::getType($name);

        $classInfo = (new BetterReflection())
            ->classReflector()
            ->reflect(\get_class($dbalType));

        $methodInfo = $classInfo->getMethod('convertToPHPValue');
        $returnInfo = $methodInfo->getReturnType();

        $allowsNull = false;

        foreach ($methodInfo->getDocBlockReturnTypes() as $docBlockReturnType) {
            if ($docBlockReturnType instanceof Types\Null_) {
                $allowsNull = true;
            }
        }

        foreach ($methodInfo->getDocBlockReturnTypes() as $docBlockReturnType) {
            if ($docBlockReturnType instanceof Types\Nullable) {
                $allowsNull = true;
                $docBlockReturnType = $docBlockReturnType->getActualType();
            }

            if ($docBlockReturnType instanceof Types\Object_) {
                return new TypeInfo(true, (string)$docBlockReturnType->getFqsen(), $allowsNull);
            }

            if ($docBlockReturnType instanceof Types\String_) {
                return new TypeInfo(false, (string)$docBlockReturnType, $allowsNull);
            }

            if ($docBlockReturnType instanceof Types\Integer) {
                return new TypeInfo(false, (string)$docBlockReturnType, $allowsNull);
            }
        }

        if (!$returnInfo && isset(self::$_typesMap[get_class($dbalType)])) {
            return new TypeInfo(false, self::$_typesMap[get_class($dbalType)], true);
        }

        if (!$returnInfo) {
            return new TypeInfo(false, $name, false);
        }

        if ($returnInfo->isBuiltin()) {
            return new TypeInfo(false, (string)$returnInfo, $returnInfo->allowsNull());
        }

        return new TypeInfo(true, $returnInfo->targetReflectionClass()->getName(), $returnInfo->allowsNull());
    }
}
