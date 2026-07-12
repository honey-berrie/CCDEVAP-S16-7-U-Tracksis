<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

$_SESSION = [];
session_destroy();

json([
    'success' => true,
    'message' => 'Logged out successfully',
]);

?>