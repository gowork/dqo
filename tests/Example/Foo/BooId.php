<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\Foo;

final class BooId
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function from($value): self
    {
        return new self($value);
    }
}
