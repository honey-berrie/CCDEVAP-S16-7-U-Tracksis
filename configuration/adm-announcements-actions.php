<?php

require_once "session.php";
requireAdminApi();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$action = $_POST['action'] ?? "";
$admin = getCurrentAdmin($conn);

function fail($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

switch ($action) {

    case "save": {
        $id      = (int) ($_POST['id'] ?? 0);
        $title   = trim($_POST['title'] ?? "");
        $message = trim($_POST['message'] ?? "");

        if ($title === "" || $message === "") {
            fail("Title and message are required.");
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, message=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $message, $id);
        } else {
            $authorId = $admin['id'];
            if (!$authorId) {
                fail("No admin account found to attribute this announcement to.", 500);
            }
            $stmt = $conn->prepare("
                INSERT INTO announcements (sender_id, group_id, is_broadcast, title, message)
                VALUES (?, NULL, 1, ?, ?)
            ");
            $stmt->bind_param("iss", $authorId, $title, $message);
        }

        if (!$stmt->execute()) {
            fail("Could not save this announcement: " . $conn->error, 500);
        }

        echo json_encode(["success" => true, "id" => $id > 0 ? $id : $stmt->insert_id]);
        break;
    }

    case "delete": {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) fail("Missing announcement id.");

        $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["success" => $stmt->affected_rows > 0]);
        break;
    }

    default:
        fail("Unknown action.");
}
