<?php

require_once __DIR__ . '/db.php';

function adviserGetThesisGroups(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT id AS group_id, group_name, thesis_title, "Active" AS status
         FROM teams
         ORDER BY group_name ASC'
    );

    return $statement->fetchAll();
}

function adviserGetGroupSummaries(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT t.id AS group_id, t.group_name, t.thesis_title, "Active" AS status,
                COUNT(m.id) AS total_milestones,
                SUM(CASE WHEN m.status = "approved" THEN 1 ELSE 0 END) AS completed_milestones,
                (
                    SELECT m2.name
                    FROM milestones m2
                    WHERE m2.group_id = t.id
                      AND m2.status <> "approved"
                    ORDER BY m2.due_date ASC, m2.display_order ASC, m2.id ASC
                    LIMIT 1
                ) AS next_milestone
         FROM teams t
         LEFT JOIN milestones m ON t.id = m.group_id
         GROUP BY t.id, t.group_name, t.thesis_title
         ORDER BY t.group_name ASC'
    );

    return array_map(function ($group) {
        $total = (int) $group['total_milestones'];
        $completed = (int) $group['completed_milestones'];
        $group['progress_percent'] = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        $group['next_milestone'] = $group['next_milestone'] ?: 'All milestones completed';

        return $group;
    }, $statement->fetchAll());
}

function adviserGetMilestoneGroups(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT t.id AS group_id, t.group_name, t.thesis_title,
                m.id AS milestone_id,
                m.name AS milestone_name,
                CASE
                    WHEN m.status = "approved" THEN "Completed"
                    WHEN m.status = "rejected" THEN "Revision"
                    WHEN m.status = "in-review" THEN "In Progress"
                    ELSE "Pending"
                END AS status,
                m.due_date,
                DATE(m.completed_at) AS submitted_at,
                m.description AS comments
         FROM teams t
         LEFT JOIN milestones m ON t.id = m.group_id
         ORDER BY t.group_name ASC, m.due_date ASC, m.display_order ASC, m.id ASC'
    );

    $groups = [];

    foreach ($statement->fetchAll() as $row) {
        $groupId = (int) $row['group_id'];

        if (!isset($groups[$groupId])) {
            $groups[$groupId] = [
                'group_id' => $groupId,
                'group_name' => $row['group_name'],
                'thesis_title' => $row['thesis_title'],
                'milestones' => [],
            ];
        }

        if ($row['milestone_id'] !== null) {
            $groups[$groupId]['milestones'][] = [
                'milestone_id' => (int) $row['milestone_id'],
                'milestone_name' => $row['milestone_name'],
                'status' => $row['status'],
                'due_date' => $row['due_date'],
                'submitted_at' => $row['submitted_at'],
                'comments' => $row['comments'],
            ];
        }
    }

    return array_values($groups);
}

function adviserGetSubmissions(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT s.id AS submission_id,
                s.group_id,
                s.title,
                s.file_name,
                CASE
                    WHEN s.status = "approved" THEN "Approved"
                    WHEN s.status = "rejected" THEN "Rejected"
                    WHEN s.status = "revision-requested" THEN "Revision"
                    ELSE "Pending"
                END AS status,
                s.uploaded_at AS submitted_at,
                s.review_notes AS adviser_feedback,
                t.group_name
         FROM submissions s
         INNER JOIN teams t ON s.group_id = t.id
         ORDER BY t.group_name ASC, s.uploaded_at DESC, s.id DESC'
    );

    return $statement->fetchAll();
}

function adviserGetConsultations(PDO $pdo, string $status): array
{
    $statusList = $status === 'Scheduled'
        ? ['pending', 'approved']
        : ['completed'];

    $placeholders = implode(',', array_fill(0, count($statusList), '?'));
    $statement = $pdo->prepare(
        'SELECT c.id AS consultation_id,
                c.proposed_schedule AS consultation_date,
                c.consultation_end,
                c.meeting_link,
                CASE
                    WHEN c.status = "completed" THEN COALESCE(NULLIF(c.adviser_notes, ""), NULLIF(c.topic, ""), c.agenda, "Consultation")
                    ELSE COALESCE(NULLIF(c.topic, ""), c.agenda, "Consultation")
                END AS notes,
                c.reschedule_reason,
                CASE
                    WHEN c.status = "completed" THEN "Completed"
                    ELSE "Scheduled"
                END AS status,
                t.group_name,
                t.thesis_title
         FROM consultations c
         INNER JOIN teams t ON c.group_id = t.id
         WHERE c.status IN (' . $placeholders . ')
         ORDER BY c.proposed_schedule ' . ($status === 'Scheduled' ? 'ASC' : 'DESC')
    );
    $statement->execute($statusList);

    return $statement->fetchAll();
}

function adviserBuildConsultationRange(string $date, string $startTime, string $endTime): array
{
    $start = new DateTime($date . ' ' . $startTime);
    $end = new DateTime($date . ' ' . $endTime);

    if ($end <= $start) {
        $end->modify('+1 day');
    }

    return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

function adviserHandlePost(PDO $pdo, string $redirectPage): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update-submission-status') {
        $statusMap = [
            'Approved' => 'approved',
            'Rejected' => 'rejected',
            'Revision' => 'revision-requested',
            'Pending' => 'in-review',
        ];
        $postedStatus = $_POST['status'] ?? 'Pending';

        $statement = $pdo->prepare(
            'UPDATE submissions
             SET status = :status,
                 review_notes = :feedback,
                 reviewed_at = NOW()
             WHERE id = :submission_id'
        );
        $statement->execute([
            'status' => $statusMap[$postedStatus] ?? 'in-review',
            'feedback' => trim($_POST['feedback'] ?? ''),
            'submission_id' => (int) ($_POST['submission_id'] ?? 0),
        ]);

        header('Location: ' . $redirectPage . '?saved=status');
        exit;
    }

    if ($action === 'update-consultation-comment') {
        $statement = $pdo->prepare(
            'UPDATE consultations
             SET adviser_notes = :comment
             WHERE id = :consultation_id'
        );
        $statement->execute([
            'comment' => trim($_POST['comment'] ?? ''),
            'consultation_id' => (int) ($_POST['consultation_id'] ?? 0),
        ]);

        header('Location: ' . $redirectPage . '?saved=comment');
        exit;
    }

    if ($action === 'update-consultation-schedule') {
        [$start, $end] = adviserBuildConsultationRange(
            trim($_POST['consultation_date'] ?? ''),
            trim($_POST['consultation_time'] ?? ''),
            trim($_POST['consultation_end_time'] ?? '')
        );

        $statement = $pdo->prepare(
            'UPDATE consultations
             SET proposed_schedule = :consultation_date,
                 consultation_end = :consultation_end,
                 reschedule_reason = :reason
             WHERE id = :consultation_id'
        );
        $statement->execute([
            'consultation_date' => $start,
            'consultation_end' => $end,
            'reason' => trim($_POST['reason'] ?? ''),
            'consultation_id' => (int) ($_POST['consultation_id'] ?? 0),
        ]);

        header('Location: ' . $redirectPage . '?saved=schedule');
        exit;
    }

    if ($action === 'create-consultation') {
        [$start, $end] = adviserBuildConsultationRange(
            trim($_POST['consultation_date'] ?? ''),
            trim($_POST['consultation_time'] ?? ''),
            trim($_POST['consultation_end_time'] ?? '')
        );

        $statement = $pdo->prepare(
            'INSERT INTO consultations (
                group_id, user_id, recipient_id, topic, agenda, proposed_schedule,
                consultation_end, meeting_link, reschedule_reason, status
             )
             VALUES (
                :group_id,
                COALESCE((SELECT id FROM users WHERE role = "student" ORDER BY id ASC LIMIT 1), 1),
                COALESCE((SELECT id FROM users WHERE role = "adviser" ORDER BY id ASC LIMIT 1), 1),
                :topic,
                :agenda,
                :consultation_date,
                :consultation_end,
                :meeting_link,
                NULL,
                :status
             )'
        );
        $statement->execute([
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'topic' => trim($_POST['agenda'] ?? ''),
            'consultation_date' => $start,
            'consultation_end' => $end,
            'meeting_link' => trim($_POST['meeting_link'] ?? ''),
            'agenda' => trim($_POST['agenda'] ?? ''),
            'status' => 'approved',
        ]);

        header('Location: ' . $redirectPage . '?saved=created');
        exit;
    }
}
