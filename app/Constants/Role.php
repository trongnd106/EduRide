<?php


namespace App\Constants;


class Role
{
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_USER = 'USER';

    const ROLE_MAP = [
        1 => self::ROLE_ADMIN,
        2 => self::ROLE_USER
    ];
}
