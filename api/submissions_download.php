<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();
$id = (int) ($_GET['id'] ?? 0);
if (!$id) 
    error('id required', 422);

$stmt = $pdo->prepare("
    SELECT s.file_name, s.file_path, s.mime_type, s.file_size, s.group_id
    FROM submissions s WHERE s.id = ?
");
$stmt->execute([$id]);
$file = $stmt->fetch();

if (!$file) error('Not found', 404);

// file path
$path = __DIR__ . '/../uploads/' . $file['file_path'];
if (!file_exists($path)) error('File missing on disk', 404);


$disposition = !empty($_GET['download']) ? 'attachment' : 'inline';

header('Content-Type: '. ($file['mime_type'] ?: 'application/pdf'));
header('Content-Disposition: ' . $disposition . '; filename="' . basename($file['file_name']) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;
