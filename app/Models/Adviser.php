<?php

namespace App\Models;

use App\Core\Model;

// handles all user related database queries
class Adviser extends Model
{
    public static function getAllAdvisers(): array
    {
        $stmt = static::db()->prepare(
            'SELECT id, firstname, lastname, email, role
             FROM users
             WHERE role = :role'
        );
        $stmt->execute(['role' => 'adviser']);

        // return the full name of the adviser
        $advisers = [];
        while ($row = $stmt->fetch()) {
            $row['full_name'] = trim($row['firstname'] . ' ' . $row['lastname']);
            $advisers[] = $row;
        }
        return $advisers;
    }

}