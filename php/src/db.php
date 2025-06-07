<?php
declare(strict_types=1);

class DB
{
    public static function connect(): PDO
    {
        static $db = null;
        if ($db === null):
            $db = new \PDO('sqlite:' . __DIR__ . '/../db.sqlite');
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        endif;
        return $db;
    }
}