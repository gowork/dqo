<?php declare(strict_types=1);

namespace GW\DQO\Generator;

final class TypeInfo
{
    /** @var bool */
    private $isClass;

    /** @var string */
    private $phpType;

    public function __construct(bool $isClass, string $phpType)
    {
        $this->isClass = $isClass;
        $this->phpType = $phpType;
    }

    public function isClass(): bool
    {
        return $this->isClass;
    }

    public function phpType(): string
    {
        return $this->phpType;
    }
}
