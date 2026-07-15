<?php

require_once "session.php";
requireAdminApi();

$stmt = $conn->prepare("
    SELECT a.id, a.title, a.message, a.created_at,
           CONCAT(u.firstname, ' ', u.lastname) author
    FROM announcements a
    LEFT JOIN users u ON u.id = a.sender_id
    ORDER BY a.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

$list = [];
while ($row = $result->fetch_assoc()) {
    $list[] = [
        "id" => (int) $row['id'],
        "title" => $row['title'],
        "message" => $row['message'],
        "createdAt" => $row['created_at'],
        "author" => $row['author'] ?: "Admin",
    ];
}

header("Content-Type: application/json");
echo json_encode(["announcements" => $list]);
