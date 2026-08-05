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
            $progressSum += $m['progress'];
            $doneCount = ($m['status'] === 'approved') ? $doneCount + 1 : $doneCount;
            $nextDeadlineDays = Team::updateNextDeadline($nextDeadlineDays, $m);
        }
        unset($m);

        return [
            'milestones' => $milestones,
            'overall_progress' => $totalCount > 0 ? (int) round($progressSum / $totalCount) : 0,
            'done_count' => $doneCount,
            'total_count' => $totalCount,
            'next_deadline_days' => $nextDeadlineDays,
        ];
    }


    public static function decorateMilestone(array &$m) {
        $m['progress'] = (int) $m['progress'];
        $m['status_label'] = Helpers::statusLabel($m['status']);
        $m['status_class'] = Helpers::statusClass($m['status']);
        $m['due_date_formatted'] = $m['due_date'] ? date('M j, Y', strtotime($m['due_date'])) : null;
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

    public static function getTeamInfo($groupId): array
    {
        $stmt = static::db()->prepare(
            "SELECT id, group_name, thesis_title, abstract, adviser_id
            FROM teams
            WHERE id = ?"
        );
        $stmt->execute([$groupId]);
        return $stmt->fetch();
    }

    public static function getTeamAdviser($groupId): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT u.id, u.firstname, u.lastname
            FROM users u
            JOIN teams g ON g.adviser_id = u.id
            WHERE g.id = ? LIMIT 1"
        );
        $stmt->execute([$groupId]);
        $adviser = $stmt->fetch();

        if ($adviser) {
            $adviser['initials'] = Helpers::initialsOf($adviser['firstname'] ?? '', $adviser['lastname'] ?? '');
        }

        return $adviser ?: null;
    }

    public static function getTeamMembers($groupId): array
    {
        $stmt = static::db()->prepare(
            "SELECT u.id, u.firstname, u.lastname, u.email
            FROM users u
            JOIN group_members gm ON u.id = gm.user_id
            WHERE gm.group_id = ?"
        );
        $stmt->execute([$groupId]);
        $members = $stmt->fetchAll();

        foreach ($members as &$m) {
            $m['initials'] = Helpers::initialsOf($m['firstname'] ?? '', $m['lastname'] ?? '');
        }
        unset($m);

        return $members;
    }

    public static function updateTeamInfo(int $groupId, string $groupName, string $thesisTitle, string $abstract): bool
    {
        $stmt = static::db()->prepare(
            'UPDATE teams SET group_name = :group_name, thesis_title = :thesis_title, abstract = :abstract WHERE id = :id'
        );
        return $stmt->execute([
            'group_name' => $groupName,
            'thesis_title' => $thesisTitle,
            'abstract' => $abstract,
            'id' => $groupId,
        ]);
    }

    public static function getTeamSubmissions(int $groupId): array
    {
        $stmt = static::db()->prepare(
            "SELECT s.id, s.document_type, s.title, s.file_name, s.file_path, s.file_size, s.mime_type, s.uploaded_at, s.status, s.uploaded_by,
                    u.firstname, u.lastname
            FROM submissions s
            JOIN users u ON u.id = s.uploaded_by
            WHERE s.group_id = ?
            ORDER BY s.uploaded_at DESC"
        );
        $stmt->execute([$groupId]);
        $submissions = $stmt->fetchAll();

        foreach ($submissions as &$s) {
            $s['uploader_name'] = trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''));
            $s['status_class'] = Helpers::statusClass($s['status']);
            $s['document_type_label'] = Helpers::getSubmissionDocumentType($s['document_type']);
            $s['uploaded_at_formatted'] = date('M j, Y', strtotime($s['uploaded_at']));
            $s['status_label'] = Helpers::statusLabel($s['status']);
            unset($s['firstname'], $s['lastname']);
        }
        unset($s);

        return $submissions;
    }

    public static function getSubmissionFile(int $id): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT file_name, file_path, mime_type, file_size
             FROM submissions
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function uploadSubmission(int $groupId, string $documentType, array $file, int $uploadedBy): bool
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (mime_content_type($file['tmp_name']) !== 'application/pdf') {
            return false;
        }

        if ($file['size'] > 25 * 1024 * 1024) {
            return false;
        }

        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $uniqueFileName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
        $filePath = $uploadDir . $uniqueFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return false;
        }

        $title = Helpers::getSubmissionDocumentType($documentType);

        $stmt = static::db()->prepare(
            "INSERT INTO submissions (group_id, document_type, title, file_name, file_path, file_size, mime_type, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $groupId,
            $documentType,
            $title,
            $file['name'],
            $uniqueFileName,
            $file['size'],
            $file['type'] ?? 'application/pdf',
            $uploadedBy,
        ]);

        $stmt = static::db()->prepare(
            "INSERT INTO activities (group_id, user_id, type, description)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $groupId,
            $uploadedBy,
            'submission_uploaded',
            $_SESSION['user_name'] . ' uploaded ' . $title,
        ]);

        return true;
    }

    public static function getTeamFeedback(int $groupId): array
    {
        $stmt = static::db()->prepare(
            "SELECT f.id, f.group_id, f.submission_id, f.given_by,
                f.author_role, f.message, f.created_at,
                u.firstname, u.lastname,
                s.title AS submission_title,
                s.document_type,
                s.file_name AS submission_file
            FROM feedback f
            JOIN users u ON u.id = f.given_by
            LEFT JOIN submissions s ON s.id = f.submission_id
            WHERE f.group_id = ?
            ORDER BY f.created_at DESC"
        );
        $stmt->execute([$groupId]);
        $feedbacks = $stmt->fetchAll();

        foreach ($feedbacks as &$fb) {
            $fb['author'] = trim(($fb['firstname'] ?? '') . ' ' . ($fb['lastname'] ?? ''));
            $fb['initials'] = Helpers::initialsOf($fb['firstname'] ?? '', $fb['lastname'] ?? '');
            $fb['time_ago'] = Helpers::timeAgo(strtotime($fb['created_at']));
            $fb['chapter'] = $fb['submission_title'];
            $fb['date_formatted'] = date('M j, Y', strtotime($fb['created_at']));
            unset($fb['firstname'], $fb['lastname']);
        }
        unset($fb);

        $grouped = [];
        foreach ($feedbacks as $fb) {
            $chap = $fb['chapter'];
            if (!isset($grouped[$chap])) {
                $grouped[$chap] = [
                    'id' => $fb['id'],
                    'document_type_label' => Helpers::getSubmissionDocumentType($fb['document_type']),
                    'chapter' => $chap,
                    'count' => 0,
                    'last_updated' => Helpers::timeAgo(strtotime($fb['created_at'])),
                    'items' => [],
                    'messages' => [],
                ];
            }
            $grouped[$chap]['count']++;
            $grouped[$chap]['items'][] = $fb;
            // include the initials, author, time_ago, author_role, and message for each feedback in the messages array
            $grouped[$chap]['messages'][] = [
                'initials' => $fb['initials'],
                'author' => $fb['author'],
                'time_ago' => $fb['time_ago'],
                'author_role' => $fb['author_role'],
                'message' => $fb['message'],
            ];
        }

        return $grouped;
    }

    public static function getTeamAnnouncements(int $userId, int $groupId): array
    {
        // check if the announcement is read by the user and also the author whole name and role
        $stmt = static::db()->prepare(
            'SELECT a.id, a.title, a.message, a.sender_id, a.created_at,
                    u.firstname, u.lastname,
                    CASE WHEN ar.user_id IS NULL THEN 0 ELSE 1 END AS is_read
             FROM announcements a
             LEFT JOIN announcement_reads ar ON a.id = ar.announcement_id AND ar.user_id = :user_id
             LEFT JOIN users u ON u.id = a.sender_id
             WHERE a.group_id = :group_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId, 'group_id' => $groupId]);
        $announcements = $stmt->fetchAll();

        foreach ($announcements as &$a) {
            $a['author_initials'] = Helpers::initialsOf($a['firstname'] ?? '', $a['lastname'] ?? '');
            $a['author'] = trim(($a['firstname'] ?? '') . ' ' . ($a['lastname'] ?? ''));
            $a['time_ago'] = Helpers::timeAgo(strtotime($a['created_at']));
            $a['date_formatted'] = date('M j, Y', strtotime($a['created_at']));
            unset($a['firstname'], $a['lastname']);
        }
        unset($a);

        return $announcements;
    }

    public static function getTeamConsultations(int $groupId): array
    {
        /*
CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    recipient_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    agenda TEXT NULL,
    proposed_schedule DATETIME NULL,
    consultation_end DATETIME NULL,
    meeting_link VARCHAR(500) NULL,
    status ENUM('pending','approved','completed','cancelled') NOT NULL DEFAULT 'pending',
    adviser_notes TEXT NULL,
    reschedule_reason TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cons_group (group_id),
    INDEX idx_cons_user (user_id),
    INDEX idx_cons_recipient (recipient_id),
    INDEX idx_cons_status (status),
    INDEX idx_cons_created (created_at),
    CONSTRAINT fk_cons_group FOREIGN KEY (group_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_cons_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cons_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


        */

        // above is the table for consultations
        $stmt = static::db()->prepare(
            "SELECT c.id, c.group_id, c.user_id, c.recipient_id, c.topic, c.agenda,
                    c.proposed_schedule, c.consultation_end, c.meeting_link,
                    c.status, c.adviser_notes, c.reschedule_reason,
                    c.created_at, c.updated_at,
                    u.firstname, u.lastname
            FROM consultations c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.group_id = ?
            ORDER BY c.created_at DESC"
        );

        $stmt->execute([$groupId]);
        $consultations = $stmt->fetchAll();

        //date_formatted, time_formatted, topic, recipient_name, agenda, status_class, status_label
        foreach ($consultations as &$c) {
            $c['author'] = trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? ''));
            $c['initials'] = Helpers::initialsOf($c['firstname'] ?? '', $c['lastname'] ?? '');
            $c['time_ago'] = Helpers::timeAgo(strtotime($c['created_at']));
            $c['date_formatted'] = date('M j, Y', strtotime($c['created_at']));
            $c['proposed_schedule_formatted'] = $c['proposed_schedule'] ? date('M j, Y g:i A', strtotime($c['proposed_schedule'])) : null;
            $c['consultation_end_formatted'] = $c['consultation_end'] ? date('M j, Y g:i A', strtotime($c['consultation_end'])) : null;
            $c['status_label'] = Helpers::statusLabel($c['status']);
            $c['status_class'] = Helpers::statusClass($c['status']);
            unset($c['firstname'], $c['lastname']);
        }
        unset($c);

        return $consultations;
    }

    public static function createConsultationRequest(
        int $groupId,
        int $userId,
        int $recipientId,
        string $topic,
        ?string $agenda,
        ?string $proposedSchedule
    ): ?array {
        $stmt = static::db()->prepare(
            "INSERT INTO consultations (group_id, user_id, recipient_id, topic, agenda, proposed_schedule, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );

        $ok = $stmt->execute([
            $groupId,
            $userId,
            $recipientId,
            $topic,
            $agenda !== '' ? $agenda : null,
            $proposedSchedule,
        ]);

        if (!$ok) {
            return null;
        }

        $id = (int) static::db()->lastInsertId();

        $stmt = static::db()->prepare(
            "SELECT c.id, c.group_id, c.user_id, c.recipient_id, c.topic, c.agenda,
                    c.proposed_schedule, c.consultation_end, c.meeting_link,
                    c.status, c.adviser_notes, c.reschedule_reason,
                    c.created_at, c.updated_at,
                    r.firstname AS recipient_firstname, r.lastname AS recipient_lastname
            FROM consultations c
            LEFT JOIN users r ON r.id = c.recipient_id
            WHERE c.id = ?"
        );
        $stmt->execute([$id]);
        $c = $stmt->fetch();

        if (!$c) {
            return null;
        }

        $c['recipient_name'] = trim(($c['recipient_firstname'] ?? '') . ' ' . ($c['recipient_lastname'] ?? '')) ?: 'Unassigned';
        $c['date_formatted'] = date('M j, Y', strtotime($c['created_at']));
        $c['time_formatted'] = $c['proposed_schedule'] ? date('g:i A', strtotime($c['proposed_schedule'])) : 'Unscheduled';
        $c['status_label'] = Helpers::statusLabel($c['status']);
        $c['status_class'] = Helpers::statusClass($c['status']);
        unset($c['recipient_firstname'], $c['recipient_lastname']);

        return $c;
    }
}
