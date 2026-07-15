<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();

    
$g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
$g->execute([$user['id']]);
$gid = $g->fetchColumn();

if (!$gid) 
    json(['items' => [], 'total' => 0]);

$limit = 5;

$sql = "SELECT a.id, a.group_id, a.user_id, a.type, a.description, a.created_at,
               u.firstname, u.lastname, u.avatar_url
        FROM activities a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE a.group_id = ?
        ORDER BY a.created_at DESC
        LIMIT $limit";

$stmt = $pdo->prepare($sql);
$stmt->execute([$gid]);
$rows = $stmt->fetchAll();

foreach ($rows as &$r) {
    $r['actor'] = trim(($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? ''));
    $r['icon'] = iconForType($r['type']);
    unset($r['firstname'], $r['lastname']);
}

json([
    'items' => $rows,
    'total' => count($rows),
]);

