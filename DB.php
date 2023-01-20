<?php

// TODO use ini_get ou getenv to avoid put password into code
define("DB_SERVER", "localhost");
define("DB_PORT", 3306);
define("DB_USER", "root");
define("DB_PASSWD", "");
define("DB_NAME","notifications");

class DB {
    public static $db = null;

    public static function getInstance() {
        if (self::$db === null) {
            self:$db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USER, DB_PASSWD);
        }
        return $db;
    }
}