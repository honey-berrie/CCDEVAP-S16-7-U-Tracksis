<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {
    $role = $user['role'];
    
    $submissionId = !empty($_GET['submission_id']) ? (int) $_GET['submission_id'] : null;

    $g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
    $g->execute([$user['id']]);
    $gid = $g->fetchColumn();

    $sql = "SELECT f.id, f.group_id, f.submission_id, f.given_by,
                f.author_role, f.message, f.created_at,
                u.firstname, u.lastname,
                s.title AS submission_title,
                s.document_type,
                s.file_name AS submission_file
            FROM feedback f
            JOIN users u ON u.id = f.given_by
            LEFT JOIN submissions s ON s.id = f.submission_id
            WHERE f.group_id = ?
            ORDER BY f.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['author'] = trim(($r['firstname'] ?? '') . ' ' .($r['lastname'] ?? ''));
        $r['initials'] = initialsOf($r['firstname'] ?? '', $r['lastname'] ?? '');
        $r['time_ago'] = timeAgo(strtotime($r['created_at']));
        $r['chapter'] = $r['submission_title'];
        unset($r['firstname'], $r['lastname']);
    }
    unset($r);

    $grouped = [];
    foreach ($rows as $r) {
        $chap = $r['chapter'];
        if (!isset($grouped[$chap])) {
        $grouped[$chap] = [
                'chapter' => $chap,
                'count' => 0,
                'last_updated' => timeAgo(strtotime($r['created_at'])),
                'items' => [],
            ];
        }
        $grouped[$chap]['count']++;
        $grouped[$chap]['items'][] = $r;
    }

    json([
        'items' => $rows,
        'total' => count($rows),
        'grouped' => array_values($grouped),
    ]);
}
