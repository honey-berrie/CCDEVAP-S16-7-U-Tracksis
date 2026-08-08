<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/connect.php";

function getCurrentAdmin($conn)
{
    if (!empty($_SESSION['user']['id'])) {
        $userId = (int) $_SESSION['user']['id'];
        $stmt = $conn->prepare(
            "SELECT id, role, firstname, lastname, email
             FROM users
             WHERE id = ? AND role = 'admin' AND is_active = 1
             LIMIT 1"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    return [
        "id" => null,
        "role" => "admin",
        "firstname" => "Admin",
        "lastname" => "",
        "email" => null,
    ];
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['user']['id']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) {
        header("Location: ../views/Admin/adm-dashboard.php");
        exit;
    }

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
}

function requireAdminApi(): void
{
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Not logged in"]);
        exit;
    }
}
