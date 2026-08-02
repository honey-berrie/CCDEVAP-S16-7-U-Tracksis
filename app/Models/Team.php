<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Helpers;

// handles all user related database queries
class Team extends Model
{
    public static function getTeamMilestones($groupId): array
    {
        $stmt = static::db()->prepare(
            "SELECT id, name, description, due_date, progress, status, display_order
            FROM milestones
            WHERE group_id = :group_id
            ORDER BY display_order ASC"
        );
        $stmt->execute(['group_id' => $groupId]);
        $milestones = $stmt->fetchAll();

        $totalCount = count($milestones);
        $doneCount = 0;
        $progressSum = 0;
        $nextDeadlineDays = null;

        foreach ($milestones as &$m) {
            Team::decorateMilestone($m);
            $progressSum     += $m['progress'];
            $doneCount        = ($m['status'] === 'approved') ? $doneCount + 1 : $doneCount;
            $nextDeadlineDays = Team::updateNextDeadline($nextDeadlineDays, $m);
        }
        unset($m);

        return [
            'milestones'         => $milestones,
            'overall_progress'   => $totalCount > 0 ? (int) round($progressSum / $totalCount) : 0,
            'done_count'         => $doneCount,
            'total_count'        => $totalCount,
            'next_deadline_days' => $nextDeadlineDays,
        ];
    }


    public static function decorateMilestone(array &$m) {
        $m['progress']           = (int) $m['progress'];
        $m['status_label']       = Helpers::statusLabel($m['status']);
        $m['status_class']       = Helpers::statusClass($m['status']);
        $m['due_date_formatted'] = $m['due_date']
            ? date('M j, Y', strtotime($m['due_date']))
            : null;
    }

    public static function updateNextDeadline($current, array $m) {
        if (!$m['due_date'] || $m['status'] === 'approved') {
            return $current;
        }

        $daysLeft = (int) ceil((strtotime($m['due_date']) - time()) / 86400);

        // First valid one
        if ($current === null) {
            return $daysLeft;
        }

        // Prefer non-overdue that is closer than current non-overdue
        if ($daysLeft >= 0 && ($current < 0 || $daysLeft < $current)) {
            return $daysLeft;
        }

        // If current is overdue, pick the least overdue (closest to 0)
        if ($current < 0 && $daysLeft > $current) {
            return $daysLeft;
        }

        return $current;
    }

    public static function getTeamDefenseInfo($groupId) {
        $stmt = static::db()->prepare("SELECT defense_date FROM teams WHERE id = ?");
        $stmt->execute([$groupId]);
        $defenseDate = $stmt->fetchColumn();

        if (!$defenseDate) {
            return ['days_to_defense' => null, 'date_formatted' => null];
        }

        $defTs = strtotime($defenseDate);
        return [
            'days_to_defense' => (int) ceil(($defTs - time()) / 86400),
            'date_formatted'  => date('M j, Y', $defTs),
        ];
    }

    public static function getLatestFeedback($gid) {
        $fbStmt = static::db()->prepare(
            "SELECT f.id, f.message, f.created_at, f.author_role,
                    u.firstname, u.lastname, u.role
            FROM feedback f
            JOIN users u ON u.id = f.given_by
            WHERE f.group_id = ? AND (f.author_role = 'adviser' OR u.role = 'adviser')
            ORDER BY f.created_at DESC
            LIMIT 1"
        );
        $fbStmt->execute([$gid]);
        $fb = $fbStmt->fetch();

        $latestFeedback = null;
        if ($fb) {
            $latestFeedback = [
                'author' => trim(($fb['firstname'] ?? '') . ' ' . ($fb['lastname'] ?? '')),
                'initials' => Helpers::initialsOf($fb['firstname'] ?? '', $fb['lastname'] ?? ''),
                'author_role' => $fb['author_role'],
                'message' => $fb['message'],
                'time_ago' => Helpers::timeAgo(strtotime($fb['created_at'])),
                'date_formatted' => date('M j, Y', strtotime($fb['created_at'])),
            ];
        }
        return $latestFeedback;
    }

    public static function getRecentActivities($groupId, $limit = 5): array
    {
        $stmt = static::db()->prepare(
            "SELECT a.id, a.group_id, a.user_id, a.type, a.description, a.created_at,
                    u.firstname, u.lastname
            FROM activities a
            LEFT JOIN users u ON u.id = a.user_id
            WHERE a.group_id = ?
            ORDER BY a.created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$groupId, $limit]);
        $activities = $stmt->fetchAll();

        foreach ($activities as &$activity) {
            $activity['actor'] = trim(($activity['firstname'] ?? '') . ' ' . ($activity['lastname'] ?? ''));
            $activity['icon'] = Helpers::iconForType($activity['type']);
            unset($activity['firstname'], $activity['lastname']);
        }
        unset($activity);

        return $activities;
    }

}