<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-settings-model.php";

use App\Models\Admin\SettingsModel;

$settingsModel = new SettingsModel($conn);

$admin = getCurrentAdmin($conn);
$settings = $settingsModel->getSystemSettings();

header("Content-Type: application/json");
echo json_encode([
    "profile" => [
        "id" => $admin['id'],
        "firstname" => $admin['firstname'],
        "lastname" => $admin['lastname'],
        "email" => $admin['email'],
    ],
    "settings" => [
        "siteName" => $settings['site_name'] ?? "U-Tracksis",
        "currentAcademicYear" => $settings['current_academic_year'] ?? "",
        "maintenanceMode" => ($settings['maintenance_mode'] ?? "0") === "1",
    ],
]);
