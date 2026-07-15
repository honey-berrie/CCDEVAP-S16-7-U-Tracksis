<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();

$in = getInput();
$groupName = trim($in['group_name'] ?? '');
$thesisTitle = trim($in['thesis_title'] ?? '');
$abstract = trim($in['abstract'] ?? '');

// validate input
if (!$groupName || !$thesisTitle || !$abstract) {
    error('Group name, thesis title, and abstract are required', 422);
}

$userId = $user['id'];

$g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
$g->execute([$userId]);
$gid = $g->fetchColumn();

if (!$gid) {
    error('You are not in a group', 400);
}

// fetch old values before update
$old = $pdo->prepare("SELECT group_name, thesis_title, abstract FROM teams WHERE id = ?");
$old->execute([$gid]);
$oldRow = $old->fetch();

if (!$oldRow) {
    error('Group not found', 404);
}

// update user group data
$stmt = $pdo->prepare(
    "UPDATE teams SET group_name = ?, thesis_title = ?, abstract = ? WHERE id = ?"
);
$stmt->execute([$groupName, $thesisTitle, $abstract, $gid]);

// to determine which fields changed
$changed = [];
if ($groupName !== $oldRow['group_name']) {
    $changed[] = 'group name';
}

if ($thesisTitle !== $oldRow['thesis_title']) {
    $changed[] = 'thesis title';
}

if ($abstract !== $oldRow['abstract']) {
    $changed[] = 'abstract';
}

// log activity only if something actually changed
if (!empty($changed)) {
    $count = count($changed);    

    if ($count === 1) {
        $desc = $changed[0];
    } elseif ($count === 2) {
        $desc = $changed[0] . ' and ' . $changed[1];
    } else {
        $desc = $changed[0] . ', ' . $changed[1] . ', and ' . $changed[2];
    }

    $actorName = trim($user['firstname'] . ' ' . $user['lastname']);
    $description = $actorName . ' updated the ' . $desc;

    $act = $pdo->prepare(
        "INSERT INTO activities (group_id, user_id, type, description) VALUES (?, ?, 'group_profile_updated', ?)"
    );
    $act->execute([$gid, $userId, $description]);
}

json(['success' => true, 'message' => 'Group updated successfully']);