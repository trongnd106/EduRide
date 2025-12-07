<?php


namespace App\Logging;


use Monolog\Handler\RotatingFileHandler;

class CustomizeRotatingFileHandler extends RotatingFileHandler
{

    /**
     * Rotates the files.
     */
    protected function rotate() : void
    {
        parent::rotate();
        $this->createSymbolicLink($this->url);
    }

    private function createSymbolicLink($url)
    {
        if (!$url || !file_exists($url)) {
            return;
        }
        $symlink = dirname($url) . '/current.log';
        if (!is_link($symlink)) {
            symlink($url, $symlink);
        } elseif (readlink($symlink) != $url) {
            unlink($symlink);
            symlink($url, $symlink);
        }
    }
}
