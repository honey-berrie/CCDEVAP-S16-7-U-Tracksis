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

    public static function getGlobalAnnouncements(int $userId): array
    {
        // check if the announcement is read by the user and get the author's name and role
        $stmt = static::db()->prepare(
            'SELECT a.id, a.title, a.message, a.created_at, u.firstname AS sender_firstname, u.lastname AS sender_lastname, u.role AS sender_role,
                    CASE WHEN ar.user_id IS NULL THEN 0 ELSE 1 END AS is_read
             FROM announcements a
             JOIN users u ON a.sender_id = u.id
             LEFT JOIN announcement_reads ar ON a.id = ar.announcement_id AND ar.user_id = :user_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        $announcements = $stmt->fetchAll();

        foreach ($announcements as &$announcement) {
            $announcement['author_initials'] = strtoupper(substr($announcement['sender_firstname'], 0, 1) . substr($announcement['sender_lastname'], 0, 1));
            $announcement['time_ago'] = Helpers::timeAgo(strtotime($announcement['created_at']));
            $announcement['author'] = $announcement['sender_firstname'] . ' ' . $announcement['sender_lastname'];
            $announcement['author_role'] = $announcement['sender_role'];
            unset($announcement['sender_firstname'], $announcement['sender_lastname'], $announcement['sender_role']);
        }
        unset($announcement);

        return $announcements;
    }

    public static function markAnnouncementAsRead(int $userId, int $announcementId): bool
    {
        // Check if the announcement is already marked as read
        $checkStmt = static::db()->prepare(
            'SELECT 1 FROM announcement_reads WHERE user_id = :user_id AND announcement_id = :announcement_id'
        );
        $checkStmt->execute(['user_id' => $userId, 'announcement_id' => $announcementId]);
        if ($checkStmt->fetch()) {
            return true; // Already marked as read
        }

        // Mark the announcement as read
        $stmt = static::db()->prepare(
            'INSERT INTO announcement_reads (user_id, announcement_id) VALUES (:user_id, :announcement_id)'
        );
        return $stmt->execute(['user_id' => $userId, 'announcement_id' => $announcementId]);
    }

    public static function isMaintenanceModeOn(): bool
    {
        $stmt = static::db()->prepare(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch();

        return $row && $row['setting_value'] === '1';
    }
}