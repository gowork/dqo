<?php declare(strict_types=1);

namespace GW\DQO\Generator;

use GW\Value\Wrap;
use ReflectionClass;
use ReflectionMethod;
use function get_class;
use function preg_match;

final class ClassInfo
{
    private ReflectionClass $class;

    public function __construct(string $class)
    {
        $this->class = new ReflectionClass($class);
    }

    public static function fromInstance(object $instance): self
    {
        return new self(get_class($instance));
    }

    public function hasPublicConstructor(): bool
    {
        $constructor = $this->class->getConstructor();

        if ($constructor === null) {
            return false;
        }

        return $constructor->isPublic();
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
