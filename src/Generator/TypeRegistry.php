<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use Doctrine\DBAL\Types\Type;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
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
            if ($docBlockReturnType instanceof Null_) {
                $allowsNull = true;
            }
        }

        foreach ($methodInfo->getDocBlockReturnTypes() as $docBlockReturnType) {
            if ($docBlockReturnType instanceof Nullable) {
                $allowsNull = true;
                $docBlockReturnType->getActualType();
            }

            if ($docBlockReturnType instanceof Object_) {
                return new TypeInfo(true, (string)$docBlockReturnType->getFqsen(), $allowsNull);
            }

            return new TypeInfo(false, (string)$docBlockReturnType, $allowsNull);
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
