<?php

namespace App\Core;

use PDO;

// provides a shared database connection to all models
abstract class Model
{
    protected static ?PDO $pdo = null;

    // set the PDO instance
    public static function setConnection(PDO $pdo): void
    {
        static::$pdo = $pdo;
    }

    // get the PDO connection
    protected static function db(): PDO
    {
        if (static::$pdo === null) {
            throw new \RuntimeException('Database connection not set. Call Model::setConnection() first.');
        }
        return static::$pdo;
    }
}