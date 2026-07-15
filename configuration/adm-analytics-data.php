<?php

require_once "session.php";

$groupId   = isset($_GET['group']) ? (int) $_GET['group'] : 0;
$adviserId = isset($_GET['adviser']) ? (int) $_GET['adviser'] : 0;
$status    = isset($_GET['status']) ? trim($_GET['status']) : "";

$subWhere = ["1=1"];
$subParams = [];
$subTypes = "";

if ($groupId > 0) {
    $subWhere[] = "s.group_id = ?";
    $subParams[] = $groupId;
    $subTypes .= "i";
}
if ($adviserId > 0) {
    $subWhere[] = "t.adviser_id = ?";
    $subParams[] = $adviserId;
    $subTypes .= "i";
}
if ($status !== "") {
    $subWhere[] = "s.status = ?";
    $subParams[] = $status;
    $subTypes .= "s";
}
$subWhereSql = implode(" AND ", $subWhere);

$response = [];

/*
|--------------------------------------------------------------------------
| Summary cards
|--------------------------------------------------------------------------
*/
$totalSql = "SELECT COUNT(*) c FROM submissions s JOIN teams t ON t.id = s.group_id WHERE $subWhereSql";
$onTimeSql = "SELECT COUNT(*) c FROM submissions s JOIN teams t ON t.id = s.group_id WHERE $subWhereSql AND s.status = 'approved'";

$stmt = $conn->prepare($totalSql);
if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
$stmt->execute();
$totalSubs = (int) $stmt->get_result()->fetch_assoc()['c'];

$stmt = $conn->prepare($onTimeSql);
if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
$stmt->execute();
$approvedSubs = (int) $stmt->get_result()->fetch_assoc()['c'];

$onTimeRate = $totalSubs > 0 ? round(($approvedSubs / $totalSubs) * 100) : 0;

$activeGroups = (int) $conn->query("SELECT COUNT(*) c FROM teams WHERE status='active'")->fetch_assoc()['c'];
$archivedTheses = (int) $conn->query("SELECT COUNT(*) c FROM teams WHERE status='archived'")->fetch_assoc()['c'];
$avgProgress = (int) ($conn->query("SELECT ROUND(AVG(progress)) c FROM milestones")->fetch_assoc()['c'] ?? 0);

$response['summary'] = [
    "onTimeRate" => $onTimeRate,
    "activeGroups" => $activeGroups,
    "archivedTheses" => $archivedTheses,
    "avgProgress" => $avgProgress,
];

/*
|--------------------------------------------------------------------------
| Submission Status Breakdown (donut)
|--------------------------------------------------------------------------
*/
$statusSql = "
    SELECT s.status, COUNT(*) total
    FROM submissions s
    JOIN teams t ON t.id = s.group_id
    WHERE $subWhereSql
    GROUP BY s.status
";
$stmt = $conn->prepare($statusSql);
if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
$stmt->execute();
$result = $stmt->get_result();
$statusBreakdown = ["in-review" => 0, "approved" => 0, "rejected" => 0, "revision-requested" => 0];
while ($row = $result->fetch_assoc()) {
    $statusBreakdown[$row['status']] = (int) $row['total'];
}
$response['statusBreakdown'] = [
    "labels" => array_keys($statusBreakdown),
    "values" => array_values($statusBreakdown),
];

/*
|--------------------------------------------------------------------------
| Submissions Over Time
|--------------------------------------------------------------------------
*/
$overTimeSql = "
    SELECT MONTH(s.uploaded_at) month, COUNT(*) total
    FROM submissions s
    JOIN teams t ON t.id = s.group_id
    WHERE $subWhereSql
    GROUP BY MONTH(s.uploaded_at)
    ORDER BY MONTH(s.uploaded_at)
";
$stmt = $conn->prepare($overTimeSql);
if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
$stmt->execute();
$result = $stmt->get_result();
$labels = [];
$values = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = date("M", mktime(0, 0, 0, (int) $row['month'], 1));
    $values[] = (int) $row['total'];
}
$response['submissionsOverTime'] = ["labels" => $labels, "values" => $values];

/*
|--------------------------------------------------------------------------
| Group Progress Comparison (respects adviser filter only — it's per-group)
|--------------------------------------------------------------------------
*/
$groupWhere = ["t.status = 'active'"];
$groupParams = [];
$groupTypes = "";
if ($adviserId > 0) {
    $groupWhere[] = "t.adviser_id = ?";
    $groupParams[] = $adviserId;
    $groupTypes .= "i";
}
if ($groupId > 0) {
    $groupWhere[] = "t.id = ?";
    $groupParams[] = $groupId;
    $groupTypes .= "i";
}
$groupWhereSql = implode(" AND ", $groupWhere);

$progressSql = "
    SELECT t.group_name, ROUND(AVG(m.progress)) avg_progress
    FROM teams t
    LEFT JOIN milestones m ON m.group_id = t.id
    WHERE $groupWhereSql
    GROUP BY t.id
    ORDER BY avg_progress DESC
    LIMIT 10
";
$stmt = $conn->prepare($progressSql);
if ($groupTypes !== "") $stmt->bind_param($groupTypes, ...$groupParams);
$stmt->execute();
$result = $stmt->get_result();
$progLabels = [];
$progValues = [];
while ($row = $result->fetch_assoc()) {
    $progLabels[] = $row['group_name'];
    $progValues[] = (int) ($row['avg_progress'] ?? 0);
}
$response['groupProgress'] = ["labels" => $progLabels, "values" => $progValues];

/*
|--------------------------------------------------------------------------
| Filter lookup lists
|--------------------------------------------------------------------------
*/
$groups = [];
$gr = $conn->query("SELECT id, group_name FROM teams WHERE status='active' ORDER BY group_name");
while ($row = $gr->fetch_assoc()) {
    $groups[] = ["id" => (int) $row['id'], "name" => $row['group_name']];
}

$advisers = [];
$ar = $conn->query("SELECT id, CONCAT(firstname,' ',lastname) name FROM users WHERE role='adviser' AND is_active=1 ORDER BY firstname");
while ($row = $ar->fetch_assoc()) {
    $advisers[] = ["id" => (int) $row['id'], "name" => $row['name']];
}

$response['filters'] = ["groups" => $groups, "advisers" => $advisers];

header("Content-Type: application/json");
echo json_encode($response);
