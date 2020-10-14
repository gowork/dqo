<?php declare(strict_types=1);

namespace tests\GW\DQO\Example\Id;

final class UserId
{
    private $id;

    private function __construct($id)
    {
        $this->id = $id;
    }

    public static function from($value): self
    {
        return new self($value);
    }
}
