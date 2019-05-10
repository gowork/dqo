<?php

namespace tests\GW\DQO;

use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Example\UserTable;

class TableTest extends TestCase
{
    public function test_default_table_name()
    {
        $userTable = new UserTable();

        self::assertEquals('user', $userTable->table());
    }

    public function test_select_all_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals(
            [
                'user.id as user_id',
                'user.email as user_email',
                'user.name as user_name',
                'user.surname as user_surname',
            ],
            $userTable->selectAll()
        );
    }

    public function test_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user', $userTable->alias());
    }

    public function test_select_field_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user.id as user_id', $userTable->selectField(UserTable::ID));
    }

    public function test_field_path_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user.id', $userTable->fieldPath(UserTable::ID));
    }

    public function test_select_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals(
            ['user.id as user_id', 'user.name as user_name', 'user.surname as user_surname'],
            $userTable->select(UserTable::ID, UserTable::NAME, UserTable::SURNAME)
        );
    }

    public function test_default_field_aliases()
    {
        $userTable = new UserTable();

        self::assertEquals('user_id', $userTable->fieldAlias(UserTable::ID));
        self::assertEquals('user_name', $userTable->fieldAlias(UserTable::NAME));
    }
}
