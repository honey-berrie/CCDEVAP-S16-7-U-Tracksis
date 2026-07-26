<?php

namespace App\Models;

use App\Core\Model;

// handles all user related database queries
class User extends Model
{
    // find a user by email
    public static function findByEmail(string $email): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT id, role, firstname, lastname, email, password_hash, is_active
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

}