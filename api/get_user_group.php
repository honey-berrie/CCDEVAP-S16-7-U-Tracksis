<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (!currentUser()) {
    error('Unauthorized', 401);
}

// fetch group data for the logged-in user
$stmt = $pdo->prepare("
    SELECT g.id, g.group_name, g.thesis_title, g.abstract
    FROM teams g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = ? LIMIT 1
");
$stmt->execute([currentUser()['id']]);
$group = $stmt->fetch();

// user is not part of any group
if (!$group) {
    json(['group' => null]);
}

// fetch group members
function getGroupMembers($groupId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.firstname, u.lastname, u.email, u.role
        FROM users u
        JOIN group_members gm ON u.id = gm.user_id
        WHERE gm.group_id = ?
    ");
    $stmt->execute([$groupId]);
    return $stmt->fetchAll();
}

// fetch adviser for the group
function getGroupAdviser($groupId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.firstname, u.lastname, u.email
        FROM users u
        JOIN teams g ON g.adviser_id = u.id
        WHERE g.id = ? LIMIT 1
    ");
    $stmt->execute([$groupId]);
    return $stmt->fetch();
}

json(['group' => $group, 'members' => getGroupMembers($group['id']), 'adviser' => getGroupAdviser($group['id'])]);