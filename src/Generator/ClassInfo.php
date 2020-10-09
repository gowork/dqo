<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use GW\Value\Wrap;
use function preg_match;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

final class ClassInfo
{
    private ReflectionClass $class;

    public function __construct(string $class)
    {
        $this->class = ReflectionClass::createFromName($class);
    }

    public function hasPublicConstructor(): bool
    {
        return $this->class->getConstructor()->isPublic();
    }

    public function firstStaticFactory(string $pattern = '/^(create|from)/'): ?string
    {
        $staticMethods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        return Wrap::array($staticMethods)
            ->filter(static function (ReflectionMethod $method) use ($pattern): bool {
                if ($method->getNumberOfParameters() !== 1) {
                    return false;
                }

                if (preg_match($pattern, $method->getShortName()) !== 1) {
                    return false;
                }

                $return = $method->getReturnType();

                return $return !== null
                    && (!$return->isBuiltin() || (string)$return === 'self');
            })
            ->map(static function (ReflectionMethod $method): string {
                return $method->getShortName();
            })
            ->first();
    }

    public function fullName(): string
    {
        return $this->class->getName();
    }

    public function shortName(): string
    {
        return $this->class->getShortName();
    }

    public function namespace(): string
    {
        return $this->class->getNamespaceName();
    }
}
