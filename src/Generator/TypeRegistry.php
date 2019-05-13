<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final class TypeRegistry
{
    public function type(string $name): TypeInfo
    {
        return new TypeInfo(false, $name);
    }
}
