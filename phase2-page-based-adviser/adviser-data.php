<?php

require_once __DIR__ . '/db.php';

function adviserGetThesisGroups(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT group_id, group_name, thesis_title, status
         FROM thesis_groups
         ORDER BY group_name ASC'
    );

    return $statement->fetchAll();
}

function adviserGetGroupSummaries(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT tg.group_id, tg.group_name, tg.thesis_title, tg.status,
                COUNT(m.milestone_id) AS total_milestones,
                SUM(CASE WHEN m.status = "Completed" THEN 1 ELSE 0 END) AS completed_milestones,
                (
                    SELECT m2.milestone_name
                    FROM milestones m2
                    WHERE m2.group_id = tg.group_id
                      AND m2.status <> "Completed"
                    ORDER BY m2.due_date ASC, m2.milestone_id ASC
                    LIMIT 1
                ) AS next_milestone
         FROM thesis_groups tg
         LEFT JOIN milestones m ON tg.group_id = m.group_id
         GROUP BY tg.group_id, tg.group_name, tg.thesis_title, tg.status
         ORDER BY tg.group_name ASC'
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
        'SELECT tg.group_id, tg.group_name, tg.thesis_title,
                m.milestone_id, m.milestone_name, m.status, m.due_date, m.submitted_at, m.comments
         FROM thesis_groups tg
         LEFT JOIN milestones m ON tg.group_id = m.group_id
         ORDER BY tg.group_name ASC, m.due_date ASC, m.milestone_id ASC'
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
        'SELECT s.submission_id, s.group_id, s.title, s.file_name, s.status, s.submitted_at, s.adviser_feedback,
                tg.group_name
         FROM submissions s
         INNER JOIN thesis_groups tg ON s.group_id = tg.group_id
         ORDER BY tg.group_name ASC, s.submitted_at DESC, s.submission_id DESC'
    );

    return $statement->fetchAll();
}

function adviserGetConsultations(PDO $pdo, string $status): array
{
    $statement = $pdo->prepare(
        'SELECT c.consultation_id, c.consultation_date, c.consultation_end, c.meeting_link,
                c.notes, c.reschedule_reason, c.status, tg.group_name, tg.thesis_title
         FROM consultations c
         INNER JOIN thesis_groups tg ON c.group_id = tg.group_id
         WHERE c.status = :status
         ORDER BY c.consultation_date ' . ($status === 'Scheduled' ? 'ASC' : 'DESC')
    );
    $statement->execute(['status' => $status]);

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
        $statement = $pdo->prepare(
            'UPDATE submissions
             SET status = :status,
                 adviser_feedback = :feedback
             WHERE submission_id = :submission_id'
        );
        $statement->execute([
            'status' => $_POST['status'] ?? 'Pending',
            'feedback' => trim($_POST['feedback'] ?? ''),
            'submission_id' => (int) ($_POST['submission_id'] ?? 0),
        ]);

        header('Location: ' . $redirectPage . '?saved=status');
        exit;
    }

    if ($action === 'update-consultation-comment') {
        $statement = $pdo->prepare(
            'UPDATE consultations
             SET notes = :comment
             WHERE consultation_id = :consultation_id'
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
             SET consultation_date = :consultation_date,
                 consultation_end = :consultation_end,
                 reschedule_reason = :reason
             WHERE consultation_id = :consultation_id'
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
            'INSERT INTO consultations (group_id, consultation_date, consultation_end, meeting_link, notes, reschedule_reason, status)
             VALUES (:group_id, :consultation_date, :consultation_end, :meeting_link, :notes, NULL, :status)'
        );
        $statement->execute([
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'consultation_date' => $start,
            'consultation_end' => $end,
            'meeting_link' => trim($_POST['meeting_link'] ?? ''),
            'notes' => trim($_POST['agenda'] ?? ''),
            'status' => 'Scheduled',
        ]);

        header('Location: ' . $redirectPage . '?saved=created');
        exit;
    }
}
