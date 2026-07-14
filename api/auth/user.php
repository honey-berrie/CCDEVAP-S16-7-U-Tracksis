<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if (!isset($_SESSION['user'])) {
    error('Unauthorized', 401);
}

// fetch user data from the database
$stmt = $pdo->prepare("SELECT id, role, firstname, lastname, email, is_active, last_login_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user']['id']]);

$user = $stmt->fetch();

if (!$user) {
    error('User not found', 404);
}

json(['user' => $user]);