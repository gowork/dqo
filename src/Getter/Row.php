<?php declare(strict_types=1);

namespace GW\DQO\Getter;

interface Row
{
    /** @return mixed */
    public function get(string $field);
}
