<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Helpers;

// handles all user related database queries
class Team extends Model
{
    // fixed document type order for milestone display
    private static function getDocumentTypeOrder(): array
    {
        return [
            'title-proposal' => 'Title Proposal',
            'chapter-1'      => 'Chapter 1',
            'chapter-2'      => 'Chapter 2',
            'chapter-3'      => 'Chapter 3',
            'chapter-4'      => 'Chapter 4',
            'chapter-5'      => 'Chapter 5',
            'final-thesis'   => 'Final Thesis',
        ];
    }

    public static function getTeamMilestones($groupId): array
    {
        // derive milestones purely from submissions
        $docTypes = static::getDocumentTypeOrder();
        $order = 1;
        $milestones = [];

        foreach ($docTypes as $docType => $label) {
            // check submission statuses for this doc type
            $stmt = static::db()->prepare(
                "SELECT s.status, s.uploaded_at, s.review_notes
                 FROM submissions s
                 WHERE s.group_id = ? AND s.document_type = ?
                 ORDER BY s.uploaded_at DESC"
            );
            $stmt->execute([$groupId, $docType]);
            $subs = $stmt->fetchAll();

            $hasApproved = false;
            $hasInReview = false;
            $hasRejected = false;
            $latestUploadedAt = null;

            foreach ($subs as $sub) {
                if ($sub['status'] === 'approved') $hasApproved = true;
                if (in_array($sub['status'], ['in-review', 'revision-requested'])) $hasInReview = true;
                if ($sub['status'] === 'rejected') $hasRejected = true;
                if (!$latestUploadedAt) $latestUploadedAt = $sub['uploaded_at'];
            }

            if ($hasApproved) {
                $status = 'approved';
                $progress = 100;
            } elseif ($hasInReview) {
                $status = 'in-review';
                $progress = 50;
            } elseif ($hasRejected) {
                $status = 'rejected';
                $progress = 0;
            } elseif (!empty($subs)) {
                $status = 'in-progress';
                $progress = 25;
            } else {
                $status = 'in-progress';
                $progress = 0;
            }

            $milestones[] = [
                'id' => 0,
                'name' => $label,
                'description' => $label,
                'due_date' => null,
                'display_order' => $order++,
                'progress' => $progress,
                'status' => $status,
            ];
        }

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

    public static function uploadSubmission(int $groupId, string $documentType, array $file, int $uploadedBy): bool|string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // validate PDF: check extension and MIME type (with fallback)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return 'not_pdf';
        }
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($file['tmp_name']);
            if ($mime !== 'application/pdf') {
                return 'not_pdf';
            }
        }

        if ($file['size'] > 25 * 1024 * 1024) {
            return 'too_large';
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


    public static function getAdviserGroups(int $adviserId): array
    {
        $stmt = static::db()->prepare(
            "SELECT t.id, t.group_name, t.thesis_title, t.progress_status, t.status,
                    t.defense_date, t.created_at,
                    s.section_code,
                    c.course_code
             FROM teams t
             LEFT JOIN sections s ON s.id = t.section_id
             LEFT JOIN courses c ON c.id = s.course_id
             WHERE t.adviser_id = ? AND t.status = 'active'
             ORDER BY t.group_name ASC"
        );
        $stmt->execute([$adviserId]);
        $groups = $stmt->fetchAll();

        foreach ($groups as &$g) {
            $g['member_count'] = static::getGroupMemberCount($g['id']);
            $g['overall_progress'] = static::getGroupOverallProgress($g['id']);
            $g['defense_date_formatted'] = $g['defense_date'] ? date('M j, Y', strtotime($g['defense_date'])) : null;
        }
        unset($g);

        return $groups;
    }

    public static function getAdviserPendingReviews(int $adviserId): int
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM submissions s
             JOIN teams t ON t.id = s.group_id
             WHERE t.adviser_id = ? AND s.status = 'in-review'"
        );
        $stmt->execute([$adviserId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getAdviserUpcomingConsultations(int $adviserId): int
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM consultations c
             WHERE c.recipient_id = ? AND c.status IN ('pending', 'approved')
               AND c.proposed_schedule >= NOW()"
        );
        $stmt->execute([$adviserId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getAdviserUnreadAlerts(int $adviserId): int
    {
        // count unread announcements for the adviser
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM announcements a
             WHERE a.sender_id != ?
               AND NOT EXISTS (
                   SELECT 1 FROM announcement_reads ar
                   WHERE ar.announcement_id = a.id AND ar.user_id = ?
               )"
        );
        $stmt->execute([$adviserId, $adviserId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getAdviserGroupProgress(int $adviserId): array
    {
        $groups = static::getAdviserGroups($adviserId);
        $progress = [];

        foreach ($groups as $g) {
            $progress[] = [
                'group_name' => $g['group_name'],
                'progress' => $g['overall_progress'],
            ];
        }

        return $progress;
    }

    public static function getAdviserRecentActivities(int $adviserId, int $limit = 5): array
    {
        $stmt = static::db()->prepare(
            "SELECT a.id, a.group_id, a.user_id, a.type, a.description, a.created_at,
                    u.firstname, u.lastname,
                    t.group_name
             FROM activities a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN teams t ON t.id = a.group_id
             WHERE t.adviser_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$adviserId, $limit]);
        $activities = $stmt->fetchAll();

        foreach ($activities as &$activity) {
            $activity['actor'] = trim(($activity['firstname'] ?? '') . ' ' . ($activity['lastname'] ?? ''));
            $activity['icon'] = Helpers::iconForType($activity['type']);
            unset($activity['firstname'], $activity['lastname']);
        }
        unset($activity);

        return $activities;
    }

    public static function getAdviserSubmissions(int $adviserId): array
    {
        $stmt = static::db()->prepare(
            "SELECT s.id AS submission_id,
                    s.group_id,
                    s.document_type,
                    s.title,
                    s.file_name,
                    s.file_path,
                    s.file_size,
                    s.mime_type,
                    s.status,
                    s.uploaded_at,
                    s.review_notes,
                    s.uploaded_by,
                    t.group_name,
                    u.firstname AS uploader_firstname,
                    u.lastname AS uploader_lastname
             FROM submissions s
             INNER JOIN teams t ON s.group_id = t.id
             LEFT JOIN users u ON u.id = s.uploaded_by
             WHERE t.adviser_id = ?
             ORDER BY s.uploaded_at DESC"
        );
        $stmt->execute([$adviserId]);
        $submissions = $stmt->fetchAll();

        foreach ($submissions as &$s) {
            $s['status_label'] = Helpers::statusLabel($s['status']);
            $s['status_class'] = Helpers::statusClass($s['status']);
            $s['document_type_label'] = Helpers::getSubmissionDocumentType($s['document_type']);
            $s['uploaded_at_formatted'] = $s['uploaded_at'] ? date('M j, Y', strtotime($s['uploaded_at'])) : null;
            $s['uploader_name'] = trim(($s['uploader_firstname'] ?? '') . ' ' . ($s['uploader_lastname'] ?? ''));
            $s['file_size_formatted'] = $s['file_size'] ? round($s['file_size'] / 1048576, 1) . ' MB' : '0 MB';
            unset($s['uploader_firstname'], $s['uploader_lastname']);
        }
        unset($s);

        return $submissions;
    }

    public static function updateSubmissionStatus(int $submissionId, string $status, string $feedback): bool
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        // update the submission
        $stmt = static::db()->prepare(
            "UPDATE submissions
             SET status = :status,
                 review_notes = :feedback,
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW()
             WHERE id = :submission_id"
        );
        $ok = $stmt->execute([
            'status' => $status,
            'feedback' => $feedback,
            'reviewed_by' => $adviserId,
            'submission_id' => $submissionId,
        ]);

        if (!$ok) return false;

        // fetch submission info for feedback
        $sub = static::db()->prepare(
            "SELECT s.group_id, s.title, t.group_name
             FROM submissions s
             JOIN teams t ON t.id = s.group_id
             WHERE s.id = ?"
        );
        $sub->execute([$submissionId]);
        $subRow = $sub->fetch();

        if (!$subRow) return true;

        // insert feedback record so it appears on student's feedback page
        $fbStmt = static::db()->prepare(
            "INSERT INTO feedback (group_id, submission_id, given_by, author_role, message)
             VALUES (?, ?, ?, 'adviser', ?)"
        );
        $fbStmt->execute([
            $subRow['group_id'],
            $submissionId,
            $adviserId,
            $feedback,
        ]);

        // insert activity
        $actStmt = static::db()->prepare(
            "INSERT INTO activities (group_id, user_id, type, description)
             VALUES (?, ?, ?, ?)"
        );
        $actStmt->execute([
            $subRow['group_id'],
            $adviserId,
            'feedback_added',
            $_SESSION['user_name'] . ' ' . $status . ' ' . $subRow['title'] . ' for ' . $subRow['group_name'],
        ]);

        return true;
    }

    public static function getAdviserMilestoneGroups(int $adviserId): array
    {
        // derive milestones purely from submissions, grouped by team
        $groups = static::getAdviserGroups($adviserId);
        $docTypes = static::getDocumentTypeOrder();
        $result = [];

        foreach ($groups as $g) {
            $groupId = $g['id'];
            $milestones = [];
            $order = 1;

            foreach ($docTypes as $docType => $label) {
                $stmt = static::db()->prepare(
                    "SELECT s.status, s.uploaded_at, s.review_notes
                     FROM submissions s
                     WHERE s.group_id = ? AND s.document_type = ?
                     ORDER BY s.uploaded_at DESC"
                );
                $stmt->execute([$groupId, $docType]);
                $subs = $stmt->fetchAll();

                $hasApproved = false;
                $hasInReview = false;
                $hasRejected = false;
                $latestUploadedAt = null;

                foreach ($subs as $sub) {
                    if ($sub['status'] === 'approved') $hasApproved = true;
                    if (in_array($sub['status'], ['in-review', 'revision-requested'])) $hasInReview = true;
                    if ($sub['status'] === 'rejected') $hasRejected = true;
                    if (!$latestUploadedAt) $latestUploadedAt = $sub['uploaded_at'];
                }

                if ($hasApproved) {
                    $status = 'approved';
                } elseif ($hasInReview) {
                    $status = 'in-review';
                } elseif ($hasRejected) {
                    $status = 'rejected';
                } elseif (!empty($subs)) {
                    $status = 'in-progress';
                } else {
                    $status = 'in-progress';
                }

                $milestones[] = [
                    'milestone_id' => 0,
                    'milestone_name' => $label,
                    'status' => $status,
                    'status_label' => Helpers::statusLabel($status),
                    'status_class' => Helpers::statusClass($status),
                    'due_date' => null,
                    'due_date_formatted' => null,
                    'submitted_at' => $latestUploadedAt,
                    'submitted_at_formatted' => $latestUploadedAt ? date('M j, Y', strtotime($latestUploadedAt)) : null,
                    'comments' => null,
                ];
            }

            $result[] = [
                'group_id' => $groupId,
                'group_name' => $g['group_name'],
                'thesis_title' => $g['thesis_title'],
                'milestones' => $milestones,
            ];
        }

        return $result;
    }

    public static function getAdviserConsultations(int $adviserId): array
    {
        $stmt = static::db()->prepare(
            "SELECT c.id, c.group_id, c.user_id, c.recipient_id, c.topic, c.agenda,
                    c.proposed_schedule, c.consultation_end, c.meeting_link,
                    c.status, c.adviser_notes, c.reschedule_reason,
                    c.created_at, c.updated_at,
                    t.group_name,
                    u.firstname, u.lastname
             FROM consultations c
             INNER JOIN teams t ON c.group_id = t.id
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.recipient_id = ?
             ORDER BY c.proposed_schedule DESC"
        );
        $stmt->execute([$adviserId]);
        $consultations = $stmt->fetchAll();

        foreach ($consultations as &$c) {
            $c['requester_name'] = trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? ''));
            $c['status_label'] = Helpers::statusLabel($c['status']);
            $c['status_class'] = Helpers::statusClass($c['status']);
            $c['proposed_schedule_formatted'] = $c['proposed_schedule'] ? date('M j, Y g:i A', strtotime($c['proposed_schedule'])) : null;
            $c['consultation_end_formatted'] = $c['consultation_end'] ? date('M j, Y g:i A', strtotime($c['consultation_end'])) : null;
            $c['month'] = $c['proposed_schedule'] ? date('M', strtotime($c['proposed_schedule'])) : '--';
            $c['day'] = $c['proposed_schedule'] ? date('d', strtotime($c['proposed_schedule'])) : '--';
            $c['time_range'] = $c['proposed_schedule']
                ? date('g:i A', strtotime($c['proposed_schedule']))
                  . ($c['consultation_end'] ? ' - ' . date('g:i A', strtotime($c['consultation_end'])) : '')
                : 'Unscheduled';
            unset($c['firstname'], $c['lastname']);
        }
        unset($c);

        return $consultations;
    }

    public static function getAdviserGroupSummaries(int $adviserId): array
    {
        // derive group summaries purely from submissions
        $groups = static::getAdviserGroups($adviserId);
        $docTypes = static::getDocumentTypeOrder();
        $totalTypes = count($docTypes);

        foreach ($groups as &$g) {
            $groupId = $g['id'];
            $completed = 0;
            $nextMilestone = null;

            foreach ($docTypes as $docType => $label) {
                $stmt = static::db()->prepare(
                    "SELECT 1 FROM submissions
                     WHERE group_id = ? AND document_type = ? AND status = 'approved'
                     LIMIT 1"
                );
                $stmt->execute([$groupId, $docType]);
                if ($stmt->fetch()) {
                    $completed++;
                } elseif ($nextMilestone === null) {
                    $nextMilestone = $label;
                }
            }

            $g['total_milestones'] = $totalTypes;
            $g['completed_milestones'] = $completed;
            $g['progress_percent'] = $totalTypes > 0 ? (int) round(($completed / $totalTypes) * 100) : 0;
            $g['next_milestone'] = $nextMilestone ?: 'All milestones completed';
            $g['member_count'] = static::getGroupMemberCount($groupId);
        }
        unset($g);

        return $groups;
    }


    private static function getGroupMemberCount(int $groupId): int
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM group_members WHERE group_id = ?"
        );
        $stmt->execute([$groupId]);
        return (int) $stmt->fetchColumn();
    }

    private static function getGroupOverallProgress(int $groupId): int
    {
        // derive progress purely from submissions: each doc type with >=1 approved = 100%
        $docTypes = static::getDocumentTypeOrder();
        $totalTypes = count($docTypes);
        if ($totalTypes === 0) return 0;

        $approvedCount = 0;
        foreach ($docTypes as $docType => $label) {
            $stmt = static::db()->prepare(
                "SELECT 1 FROM submissions
                 WHERE group_id = ? AND document_type = ? AND status = 'approved'
                 LIMIT 1"
            );
            $stmt->execute([$groupId, $docType]);
            if ($stmt->fetch()) {
                $approvedCount++;
            }
        }

        return (int) round(($approvedCount / $totalTypes) * 100);
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
