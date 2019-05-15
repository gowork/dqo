<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use GW\Value\Wrap;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

final class ClassInfo
{
    /** @var ReflectionClass */
    private $class;

    public function __construct(string $class)
    {
        $this->class = ReflectionClass::createFromName($class);
    }

    public function hasPublicConstructor(): bool
    {
        return $this->class->getConstructor()->isPublic();
    }

    public function firstStaticFactory(): ?string
    {
        $staticMethods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        return Wrap::array($staticMethods)
            ->filter(function (ReflectionMethod $method): bool {
                if ($method->getNumberOfParameters() !== 1) {
                    return false;
                }

                $return = $method->getReturnType();

                return $return !== null
                    && (!$return->isBuiltin() || (string)$return === 'self');
            })
            ->map(static function (ReflectionMethod $method): string {
                return $method->getName();
            })
            ->first();
    }

    public function shortName(): string
    {
        return $this->class->getShortName();
    }
}
