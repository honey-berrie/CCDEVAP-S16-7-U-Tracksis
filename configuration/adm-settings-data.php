<?php
/*
|--------------------------------------------------------------------------
| Settings Data Endpoint (GET)
|--------------------------------------------------------------------------
| Returns the current admin's profile fields and the system_settings
| key/value table (site name, academic year, maintenance mode).
*/

require_once "session.php";

$admin = getCurrentAdmin($conn);

$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

header("Content-Type: application/json");
echo json_encode([
    "profile" => [
        "id" => $admin['id'],
        "firstname" => $admin['firstname'],
        "lastname" => $admin['lastname'],
        "email" => $admin['email'],
        "avatarUrl" => $admin['avatar_url'],
    ],
    "settings" => [
        "siteName" => $settings['site_name'] ?? "U-Tracksis",
        "currentAcademicYear" => $settings['current_academic_year'] ?? "",
        "maintenanceMode" => ($settings['maintenance_mode'] ?? "0") === "1",
    ],
]);
