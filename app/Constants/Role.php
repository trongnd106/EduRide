<?php


namespace App\Constants;


class Role
{
    const ROLE_ADMIN = 'admin';
    const ROLE_PARENT = 'phụ huynh';
    const ROLE_ASSISTANT = 'phụ xe';

    const ROLE_MAP = [
        1 => self::ROLE_ADMIN,      // Admin - login web
        2 => self::ROLE_PARENT,     // Phụ huynh - login mobile
        3 => self::ROLE_ASSISTANT,  // Phụ xe - login mobile
    ];
}
