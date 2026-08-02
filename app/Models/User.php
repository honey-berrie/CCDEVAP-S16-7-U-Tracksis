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

    public static function register(string $email, string $firstname, string $lastname, string $password, string $account_type): string
    {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = static::db()->prepare(
            "INSERT INTO users (role, firstname, lastname, email, password_hash) VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([$account_type, $firstname, $lastname, $email, $password_hash])){
            return "Registration success, you may login now!";
        } else {
            return "Registration failed!";
        }
    }

    public static function getUserGroupId(int $userId): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT group_id
             FROM group_members
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return $row ? ['group_id' => $row['group_id']] : null;
    }

    

}