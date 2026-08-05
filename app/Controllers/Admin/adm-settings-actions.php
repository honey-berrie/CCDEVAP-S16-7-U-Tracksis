<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-settings-model.php";

use App\Models\Admin\SettingsModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$settingsModel = new SettingsModel($conn);
$admin = getCurrentAdmin($conn);
$action = $_POST['action'] ?? "";

function fail($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

if ($action === "update_profile") {
    if (!$admin['id']) {
        fail("No admin account found in the database to update.", 500);
    }

    $firstname = trim($_POST['firstname'] ?? "");
    $lastname  = trim($_POST['lastname'] ?? "");
    $email     = trim($_POST['email'] ?? "");
    $password  = $_POST['password'] ?? "";

    if ($firstname === "" || $lastname === "" || $email === "") {
        fail("First name, last name, and email are required.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail("Invalid email address.");
    }

    if (!$settingsModel->updateProfile($admin['id'], $firstname, $lastname, $email, $password)) {
        fail("Could not update your profile: " . $conn->error, 500);
    }

    echo json_encode(["success" => true]);
    exit;
}

if ($action === "update_settings") {
    $maintenance = !empty($_POST['maintenance_mode']);

    if (!$settingsModel->updateMaintenanceMode($maintenance)) {
        fail("Could not save this setting: " . $conn->error, 500);
    }

    echo json_encode(["success" => true]);
    exit;
}

fail("Unknown action.");
