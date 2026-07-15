<?php
/*
|--------------------------------------------------------------------------
| Announcements Data Endpoint (GET)
|--------------------------------------------------------------------------
| Returns every announcement, newest first.
|
| The announcements table now has no draft/published state and no
| audience column (per the group's latest schema: id, sender_id, group_id,
| is_broadcast, title, message, created_at) -- so there's nothing left to
| split into two lists or filter by audience. Admin announcements are
| always system-wide (group_id NULL, is_broadcast 1); author is resolved
| via sender_id.
*/

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
