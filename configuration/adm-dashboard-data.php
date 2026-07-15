<?php

require_once "session.php";
requireAdminApi();

$response = [];


$admin = getCurrentAdmin($conn);
$response['currentUser'] = [
    "firstname" => $admin['firstname'],
    "lastname"  => $admin['lastname'],
];


$stats = [
    "students" => 0,
    "advisers" => 0,
    "activeGroups" => 0,
    "approvedTheses" => 0,
    "pendingArchive" => 0,
    "avgProgress" => 0,
];

if ($row = $conn->query("SELECT COUNT(*) c FROM users WHERE role='student' AND is_active=1")->fetch_assoc()) {
    $stats['students'] = (int) $row['c'];
}
if ($row = $conn->query("SELECT COUNT(*) c FROM users WHERE role='adviser' AND is_active=1")->fetch_assoc()) {
    $stats['advisers'] = (int) $row['c'];
}
if ($row = $conn->query("SELECT COUNT(*) c FROM teams WHERE status='active'")->fetch_assoc()) {
    $stats['activeGroups'] = (int) $row['c'];
}
if ($row = $conn->query("SELECT COUNT(*) c FROM teams WHERE status='archived'")->fetch_assoc()) {
    $stats['approvedTheses'] = (int) $row['c'];
}

$pendingArchiveQuery = "
    SELECT COUNT(*) c FROM teams t
    WHERE t.status = 'active'
      AND EXISTS (SELECT 1 FROM milestones m WHERE m.group_id = t.id)
      AND NOT EXISTS (
            SELECT 1 FROM milestones m
            WHERE m.group_id = t.id AND m.status <> 'approved'
      )
";
if ($row = $conn->query($pendingArchiveQuery)->fetch_assoc()) {
    $stats['pendingArchive'] = (int) $row['c'];
}
if ($row = $conn->query("SELECT ROUND(AVG(progress)) c FROM milestones")->fetch_assoc()) {
    $stats['avgProgress'] = (int) $row['c'];
}

$response['stats'] = $stats;


$submissionQuery = "
    SELECT
        MONTH(uploaded_at) AS month,
        SUM(status = 'approved') AS onTime,
        SUM(status IN ('revision-requested','rejected')) AS late
    FROM submissions
    GROUP BY MONTH(uploaded_at)
    ORDER BY MONTH(uploaded_at)
";
$result = $conn->query($submissionQuery);
$labels = [];
$onTime = [];
$late = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = date("M", mktime(0, 0, 0, (int) $row['month'], 1));
    $onTime[] = (int) $row['onTime'];
    $late[] = (int) $row['late'];
}
$response['submissionData'] = [
    "labels" => $labels,
    "onTime" => $onTime,
    "late" => $late,
];


$progressQuery = "SELECT status, COUNT(*) total FROM milestones GROUP BY status";
$result = $conn->query($progressQuery);
$progress = [
    "in-progress" => 0,
    "in-review" => 0,
    "approved" => 0,
    "rejected" => 0,
];
while ($row = $result->fetch_assoc()) {
    $progress[$row['status']] = (int) $row['total'];
}
$response['progressData'] = [
    "labels" => array_keys($progress),
    "values" => array_values($progress),
];


$workloadQuery = "
    SELECT
        CONCAT(u.firstname, ' ', u.lastname) adviser,
        COUNT(t.id) workload
    FROM users u
    LEFT JOIN teams t ON t.adviser_id = u.id AND t.status = 'active'
    WHERE u.role = 'adviser' AND u.is_active = 1
    GROUP BY u.id
    ORDER BY workload DESC
";
$result = $conn->query($workloadQuery);
$labels = [];
$groups = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['adviser'];
    $groups[] = (int) $row['workload'];
}
$response['adviserData'] = [
    "labels" => $labels,
    "groups" => $groups,
];


$pendingQuery = "
    SELECT
        t.id, t.group_name, t.thesis_title,
        CONCAT(u.firstname, ' ', u.lastname) adviser,
        ROUND(AVG(m.progress)) avgProgress
    FROM teams t
    LEFT JOIN users u ON u.id = t.adviser_id
    LEFT JOIN milestones m ON m.group_id = t.id
    WHERE t.status = 'active'
      AND EXISTS (SELECT 1 FROM milestones mm WHERE mm.group_id = t.id)
      AND NOT EXISTS (SELECT 1 FROM milestones mm WHERE mm.group_id = t.id AND mm.status <> 'approved')
    GROUP BY t.id
    ORDER BY t.updated_at DESC
    LIMIT 5
";
$result = $conn->query($pendingQuery);
$pending = [];
while ($row = $result->fetch_assoc()) {
    $pending[] = [
        "id" => (int) $row['id'],
        "groupName" => $row['group_name'],
        "thesisTitle" => $row['thesis_title'],
        "adviser" => $row['adviser'] ?: "Unassigned",
        "progress" => (int) $row['avgProgress'],
    ];
}
$response['pendingApprovals'] = $pending;

$activityQuery = "
    (SELECT
        s.uploaded_at AS event_time,
        CONCAT(t.group_name, ' uploaded ', s.title) AS description
     FROM submissions s
     JOIN teams t ON t.id = s.group_id
     ORDER BY s.uploaded_at DESC
     LIMIT 5)
    UNION ALL
    (SELECT
        m.updated_at AS event_time,
        CONCAT(t.group_name, ' — ', m.name, ' marked ', m.status) AS description
     FROM milestones m
     JOIN teams t ON t.id = m.group_id
     ORDER BY m.updated_at DESC
     LIMIT 5)
    ORDER BY event_time DESC
    LIMIT 6
";
$result = $conn->query($activityQuery);
$activity = [];
while ($row = $result->fetch_assoc()) {
    $activity[] = [
        "description" => $row['description'],
        "time" => $row['event_time'],
    ];
}
$response['recentActivity'] = $activity;

$recentSubsQuery = "
    SELECT s.id, s.title, s.document_type, s.status, s.uploaded_at, t.group_name
    FROM submissions s
    JOIN teams t ON t.id = s.group_id
    ORDER BY s.uploaded_at DESC
    LIMIT 6
";
$result = $conn->query($recentSubsQuery);
$recentSubs = [];
while ($row = $result->fetch_assoc()) {
    $recentSubs[] = [
        "id" => (int) $row['id'],
        "title" => $row['title'],
        "documentType" => $row['document_type'],
        "status" => $row['status'],
        "uploadedAt" => $row['uploaded_at'],
        "groupName" => $row['group_name'],
    ];
}
$response['recentSubmissions'] = $recentSubs;

header("Content-Type: application/json");
echo json_encode($response);
