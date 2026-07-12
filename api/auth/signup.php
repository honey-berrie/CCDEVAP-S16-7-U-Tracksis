<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

$in = getInput();
$firstname = trim($in['firstname'] ?? '');
$lastname = trim($in['lastname'] ?? '');
$email = trim($in['email'] ?? '');
$pass = $in['password'] ?? '';
$passConfirm = $in['confirm'] ?? '';
$role = $in['accountType'] ?? 'student';

// validate input
if (!$firstname || !$lastname || !$email || strlen($pass) < 8) {
    error('Name, email required, password must be 8+ chars', 422);
}

// check if passwords match
if ($pass !== $passConfirm) {
    error('Passwords do not match', 422);
}

// validate role
$allowedRoles = ['student', 'adviser', 'panel', 'admin'];
$role = 'student';
if (!in_array($role, $allowedRoles, true)) {
    error('Invalid role', 422);
}

// check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    error('Email already exists', 422);
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

try {

    $stmt = $pdo->prepare(
        "INSERT INTO users (role, firstname, lastname, email, password_hash) VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->execute([$role, $firstname, $lastname, $email, $hash]);
    json([
        'success' => true,
        'message' => 'Registration successful',
        'id'      => (int) $pdo->lastInsertId(),
        'role'    => $role,
    ], 201);

    // prompt that the user has been created successfully

} catch (PDOException $e) {
    error('Failed to create user', 409);
}

?>