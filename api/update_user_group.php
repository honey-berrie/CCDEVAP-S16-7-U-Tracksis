<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';


$in = getInput();
$groupName = trim($in['group_name'] ?? '');
$thesisTitle = trim($in['thesis_title'] ?? '');
$abstract = trim($in['abstract'] ?? '');

// validate input
if (!$groupName || !$thesisTitle || !$abstract) {
    error('Group name, thesis title, and abstract are required', 422);
}

$userId = currentUser()['id'];

// update user group data
$stmt = $pdo->prepare("
    UPDATE teams t
    JOIN group_members gm ON t.id = gm.group_id
    SET t.group_name = ?, t.thesis_title = ?, t.abstract = ?
    WHERE gm.user_id = ? 
");
$stmt->execute([$groupName, $thesisTitle, $abstract, $userId]);

json(['success' => true, 'message' => 'Group updated successfully']);