<?php

namespace App\Helpers;

class CommonHelper
{
    public static function clearAllSpaces($text) {
        return str_replace([' ', "\xc2\xa0", "\xe3\x80\x80"], '', $text);
    }
}
