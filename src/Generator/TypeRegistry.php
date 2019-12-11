<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Types\Type;
use phpDocumentor\Reflection\Types as Types;
use Roave\BetterReflection\BetterReflection;

final class TypeRegistry
{
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

        if (!$returnInfo) {
            return new TypeInfo(false, $name, false);
        }

        if ($returnInfo->isBuiltin()) {
            return new TypeInfo(false, (string)$returnInfo, $returnInfo->allowsNull());
        }

        return new TypeInfo(true, $returnInfo->targetReflectionClass()->getName(), $returnInfo->allowsNull());
    }
}
