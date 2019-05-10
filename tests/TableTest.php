<?php

namespace tests\GW\DQO;

use PHPUnit\Framework\TestCase;
use tests\GW\DQO\Example\UserTable;

class TableTest extends TestCase
{
    function test_default_table_name()
    {
        $userTable = new UserTable();

        self::assertEquals('user', $userTable->table());
    }

    function test_custom_table_name()
    {
        $userTable = new UserTable('u', 'wp_user');

        self::assertEquals('wp_user', $userTable->table());
    }

    function test_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user', $userTable->alias());
    }

    function test_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals('u', $userTable->alias());
    }

    function test_select_all_with_default_alias()
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

    function test_select_all_with_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals(
            [
                'u.id as u_id',
                'u.email as u_email',
                'u.name as u_name',
                'u.surname as u_surname',
            ],
            $userTable->selectAll()
        );
    }

    function test_select_field_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user.id as user_id', $userTable->selectField(UserTable::ID));
    }

    function test_select_field_with_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals('u.id as u_id', $userTable->selectField(UserTable::ID));
    }

    function test_field_path_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user.id', $userTable->fieldPath(UserTable::ID));
    }

    function test_field_path_with_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals('u.id', $userTable->fieldPath(UserTable::ID));
    }

    function test_select_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals(
            ['user.id as user_id', 'user.name as user_name', 'user.surname as user_surname'],
            $userTable->select(UserTable::ID, UserTable::NAME, UserTable::SURNAME)
        );
    }

    function test_select_with_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals(
            ['u.id as u_id', 'u.name as u_name', 'u.surname as u_surname'],
            $userTable->select(UserTable::ID, UserTable::NAME, UserTable::SURNAME)
        );
    }

    function test_default_field_aliases()
    {
        $userTable = new UserTable();

        self::assertEquals('user_id', $userTable->fieldAlias(UserTable::ID));
        self::assertEquals('user_name', $userTable->fieldAlias(UserTable::NAME));
    }

    function test_table_own_field_paths_with_default_alias()
    {
        $userTable = new UserTable();

        self::assertEquals('user.id', $userTable->id());
        self::assertEquals('user.email', $userTable->email());
    }

    function test_table_own_field_paths_with_custom_alias()
    {
        $userTable = new UserTable('u');

        self::assertEquals('u.id', $userTable->id());
        self::assertEquals('u.email', $userTable->email());
    }
}
