<?php
// api/submissions.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {

    $groupId = $_POST['group_id'] ?? null;
    $documentType = $_POST['document_type'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $file = $_FILES['file'] ?? null;

    // validate
    if (!$groupId || !$documentType || !$title || !$file) {
        error('Group ID, document type, title, and file are required', 422);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        error('File upload error: ' . $file['error'], 400);
    }

    if (mime_content_type($file['tmp_name']) !== 'application/pdf') {
        error('Only PDF files are allowed', 400);
    }

    if ($file['size'] > 25 * 1024 * 1024) {
        error('File size exceeds the limit of 25MB', 400);
    }

    // save to disk
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $uniqueFileName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $filePath = $uploadDir . $uniqueFileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        error('Failed to move uploaded file', 500);
    }

    // insert (now includes ALL required NOT NULL columns)
    $stmt = $pdo->prepare("
        INSERT INTO submissions
            (group_id, document_type, title, file_name, file_path, file_size, mime_type, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $groupId,
        $documentType,
        $title,
        $file['name'],
        $uniqueFileName,
        $file['size'],
        $file['type'] ?? 'application/pdf',
        $user['id'],
    ]);

    // log activity
    $stmt = $pdo->prepare("
        INSERT INTO activities
            (group_id, user_id, type, description)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $groupId,
        $user['id'],
        'submission_uploaded',
        $user['firstname'] . ' ' . $user['lastname'] . ' uploaded ' . $title,
    ]);
    

    json(['success' => true, 'message' => 'Submission uploaded successfully']);
}

else if ($method === 'GET') {

    $type = $_GET['type'] ?? 'history';

    // fetch submissions for the logged-in user's group
    $stmt = $pdo->prepare("
        SELECT s.id, s.group_id, s.document_type, s.title, s.file_name, s.file_path, s.file_size, s.mime_type, s.uploaded_at, s.status, s.uploaded_by
        FROM submissions s
        JOIN group_members gm ON s.group_id = gm.group_id
        WHERE gm.user_id = ?
        ORDER BY s.uploaded_at DESC
    ");
    $stmt->execute([$user['id']]);
    $submissions = $stmt->fetchAll();

    // get uploader name
    foreach ($submissions as &$submission) {
        $uploaderStmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE id = ? LIMIT 1");
        $uploaderStmt->execute([$submission['uploaded_by']]);
        $uploader = $uploaderStmt->fetch();
        $submission['uploader_name'] = $uploader ? ($uploader['firstname'] . ' ' . $uploader['lastname']) : 'Unknown';
        // add status class for frontend
        switch ($submission['status']) {
            case 'in-review':
                $submission['status_class'] = 'badge-review';
                break;
            case 'approved':
                $submission['status_class'] = 'badge-approved';
                break;
            case 'rejected':
                $submission['status_class'] = 'badge-rejected';
                break;
            case 'revision-requested':
                $submission['status_class'] = 'badge-progress';
                break;
            default:
                $submission['status_class'] = '';
        }
    }

    json(['submissions' => $submissions]);
}

else {
    error('Method not allowed', 405);
}
