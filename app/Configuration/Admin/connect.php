<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "abc123";
$db = "u_tracksis";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "error" => "Database connection failed: " . $conn->connect_error .
                   ". Check app/Configuration/Admin/connect.php and confirm query.sql has been imported.",
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
