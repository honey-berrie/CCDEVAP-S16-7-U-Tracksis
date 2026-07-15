<?php
/*
|--------------------------------------------------------------------------
| Settings Actions Endpoint (POST)
|--------------------------------------------------------------------------
| action=update_profile  -> firstname, lastname, email, password (optional)
| action=update_settings -> maintenance_mode
*/

require_once "session.php";
requireAdminApi();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

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

    if ($password !== "") {
        // Password hashing is not required for this phase, per project scope
        // (matches the same convention already used in adm-users-actions.php).
        $stmt = $conn->prepare(
            "UPDATE users SET firstname=?, lastname=?, email=?, password_hash=? WHERE id=?"
        );
        $stmt->bind_param("ssssi", $firstname, $lastname, $email, $password, $admin['id']);
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET firstname=?, lastname=?, email=? WHERE id=?"
        );
        $stmt->bind_param("sssi", $firstname, $lastname, $email, $admin['id']);
    }

    if (!$stmt->execute()) {
        fail("Could not update your profile: " . $conn->error, 500);
    }

    echo json_encode(["success" => true]);
    exit;
}

if ($action === "update_settings") {
    $maintenance = !empty($_POST['maintenance_mode']) ? "1" : "0";

    $stmt = $conn->prepare(
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('maintenance_mode', ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );
    $stmt->bind_param("s", $maintenance);

    if (!$stmt->execute()) {
        fail("Could not save this setting: " . $conn->error, 500);
    }

    echo json_encode(["success" => true]);
    exit;
}

fail("Unknown action.");
