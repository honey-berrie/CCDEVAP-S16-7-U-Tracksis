
<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../maintenance.php';

$in = getInput();
$email = trim($in['email'] ?? '');
$pass = $in['password'] ?? '';

if (!$email || !$pass) error('Email and password required', 422);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  error('Invalid email or password', 401);
}

if ($user['role'] !== 'admin' && isMaintenanceModeOn($pdo)) {
  error('U-Tracksis is currently under maintenance. Please try again later.', 503, ['maintenance' => true]);
}

$pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
    ->execute([$user['id']]);

unset($user['password_hash']);
$_SESSION['user'] = $user;
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname'] = $user['lastname'];
$_SESSION['last_activity'] = time();

json(['message' => 'Logged in', 'user' => $user]);

?>
