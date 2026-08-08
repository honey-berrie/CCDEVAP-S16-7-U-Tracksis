<?php

session_start();
date_default_timezone_set('Asia/Manila');

$DB_HOST = 'localhost';
$DB_NAME = 'u_tracksis';
$DB_USER = 'root';
$DB_PASS = 'abc123'; // password by HONEYBERRY is 'offshore31LIME_', I am using the default XAMPP password ''

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
} catch(PDOException $e){
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

?>
