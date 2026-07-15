<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/maintenance.php';

// Intentionally no requireLogin()/requireRole() here — this endpoint has to be
// reachable by students and advisers *before* we know whether to bounce them,
// and even by the login page so it can show a heads-up before they try to log in.

$maintenance = isMaintenanceModeOn($pdo);
$user = currentUser();
$role = $user['role'] ?? null;
$exempt = $role === 'admin';

json([
    'maintenance' => $maintenance,
    'exempt' => $exempt,
    'role' => $role,
]);
