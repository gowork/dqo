<?php

use Doctrine\DBAL\Types\Type;
use tests\GW\DQO\Example\UserIdType;

require __DIR__ . '/../vendor/autoload.php';

Type::addType('UserId', UserIdType::class);
