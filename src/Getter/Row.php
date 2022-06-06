<?php declare(strict_types=1);

namespace GW\DQO\Getter;

interface Row
{
    public function get(string $field): float|bool|int|string|null;
}
